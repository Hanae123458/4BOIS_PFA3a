<?php
namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Commandes;

use App\Repository\ContactRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;


class PanierController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/panier', name: 'panier_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
        $panier = $em->getRepository(Panier::class)->findBy(['utilisateur' => $user]);

        return $this->render('panier.html.twig', [
            'panier' => $panier
        ]);
    }

    #[Route('/panier/ajouter', name: 'panier_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
        $nomProduit = $request->request->get('nom_produit');
        $prixProduit = $request->request->get('prix_produit');

        $panierExist = $em->getRepository(Panier::class)->findOneBy([
            'utilisateur' => $user,
            'nomProduit' => $nomProduit,
            'prixProduit' => $prixProduit
        ]);

        if ($panierExist) {
            $panierExist->setQuantite($panierExist->getQuantite() + 1);
            $em->flush();
            $this->addFlash('success', 'Quantité mise à jour dans votre panier');
        } else {
            $panier = new Panier();
            $panier->setUtilisateur($user)
                   ->setNomProduit($nomProduit)
                   ->setprixProduit($prixProduit)
                   ->setQuantite(1)
                   ->setDateAjout(new \DateTime());
            
            $em->persist($panier);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté au panier');
        }

         return $this->redirectToRoute('app_products');
    }

    #[Route('/panier/plus/{id}', name: 'panier_plus')]
    public function plus(int $id, EntityManagerInterface $em): Response
    {
        $panier = $em->getRepository(Panier::class)->find($id);
        
        if (!$panier) {
            throw $this->createNotFoundException('Produit non trouvé dans le panier');
        }

        $panier->setQuantite($panier->getQuantite() + 1);
        $em->flush();
        
        $this->addFlash('success', 'Quantité augmentée');

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/moins/{id}', name: 'panier_moins')]
    public function moins(int $id, EntityManagerInterface $em): Response
    {
        $panier = $em->getRepository(Panier::class)->find($id);
        
        if (!$panier) {
            throw $this->createNotFoundException('Produit non trouvé dans le panier');
        }

        if ($panier->getQuantite() > 1) {
            $panier->setQuantite($panier->getQuantite() - 1);
            $em->flush();
            $this->addFlash('warning', 'Quantité diminuée');
        } else {
            $em->remove($panier);
            $em->flush();
            $this->addFlash('warning', 'Produit retiré du panier');
        }

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
    public function supprimer(int $id, EntityManagerInterface $em): Response
    {
        $panier = $em->getRepository(Panier::class)->find($id);
        
        if (!$panier) {
            throw $this->createNotFoundException('Produit non trouvé dans le panier');
        }

        $em->remove($panier);
        $em->flush();
        $this->addFlash('danger', 'Produit supprimé du panier');

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/vider', name: 'panier_vider')]
    public function vider(EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
        $paniers = $em->getRepository(Panier::class)->findBy(['utilisateur' => $user]);

        foreach ($paniers as $panier) {
            $em->remove($panier);
        }
        $em->flush();

        $this->addFlash('danger', 'Panier vidé avec succès');
        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/passer-commande', name: 'passer_commande')]
    public function passerCommande(): void
    {
        throw new \LogicException('Intercepté automatiquement par Symfony.');
    }

    #[Route('/panier/passer-commande-check', name: 'passer_commande_check')]
public function passerCommandeCheck(
    ContactRepository $contactRepository,
    EntityManagerInterface $em
): RedirectResponse {
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Vérifie si l'utilisateur a rempli le formulaire de contact
    $hasContact = $contactRepository->findOneBy(['user' => $user]);

    if (!$hasContact) {
        $this->addFlash('error', 'Veuillez remplir le formulaire de contact d\'abord');
        return $this->redirectToRoute('contactUs');
    }

    // Calcul du prix total
    $paniers = $em->getRepository(Panier::class)->findBy(['utilisateur' => $user]);
    $total = 0;
    
    foreach ($paniers as $panier) {
        $prix = (float) str_replace('DH', '', $panier->getPrixProduit());
        $total += $prix * $panier->getQuantite();
    }

    // Création de la commande avec le statut correct
    $commande = new Commandes();
    $commande->setUtilisateur($user)
            ->setDateCommande(new \DateTimeImmutable())
            ->setStatut(Commandes::STATUT_EN_ATTENTE) // Utilisez la constante ici
            ->setPrixTotal($total);
    $em->persist($commande);

    // Vidage du panier
    foreach ($paniers as $panier) {
        $em->remove($panier);
    }
    $em->flush();

    $this->addFlash('success', 'Commande passée avec succès. Total: '.$total.' DH');
    return $this->redirectToRoute('panier_index');
}
}