<?php
namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Produits;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class ProduitsController extends AbstractController
{
    #[Route('/produitsAdmin', name: 'produits_admin')]
    public function produitsAdmin(ManagerRegistry $doctrine): Response
    {
        $produits = $doctrine->getRepository(Produits::class)->findAll();
        return $this->render('produitsAdmin.html.twig', ['produits' => $produits]);
    }

    #[Route('/produitsAdminAjout', name: 'produits_admin_ajout')]
    public function produitsAdminAjout(): Response
    {
        return $this->render('produitsAdminAjout.html.twig');
    }

    #[Route('/produitsAdminAjout/submit', name: 'ajout_produit', methods: ['POST'])]
    public function ajoutProduit(Request $request, EntityManagerInterface $entityManager)
    {
        $nomProduit = $request->request->get("nomProduit");
        $prixProduit = $request->request->get("prixProduit");
        $imageAvant = $request->files->get("imageProduitAvant");
        $imageApres = $request->files->get("imageProduitApres");

        // Validation
        if (empty($nomProduit) || empty($prixProduit) || !$imageAvant || !$imageApres) {
            $this->addFlash('error', 'Tous les champs sont obligatoires');
            return $this->redirectToRoute('produits_admin_ajout');
        }

        // Traitement des images
        $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads';
        
        // Image Avant
        $nomImageAvant = md5(uniqid()).'.'.$imageAvant->guessExtension();
        $imageAvant->move($uploadsDir, $nomImageAvant);
        
        // Image Après
        $nomImageApres = md5(uniqid()).'.'.$imageApres->guessExtension();
        $imageApres->move($uploadsDir, $nomImageApres);

        // Création du produit
        $produit = new Produits();
        $produit->setNomProduit($nomProduit);
        $produit->setPrixProduit($prixProduit);
        $produit->setImageProduitAvant($nomImageAvant);
        $produit->setImageProduitApres($nomImageApres);

        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté avec succès!');

        return $this->redirectToRoute('produits_admin');
    }

    #[Route('/produitsAdminModifier/{id}', name: 'modifier_produit')]
    public function modifierProduit(int $id, Request $request, EntityManagerInterface $em)
    {
        $produit = $em->getRepository(Produits::class)->find($id);

        if (!$produit) {
            throw $this->createNotFoundException("Produit introuvable.");
        }

        if ($request->isMethod('POST')) {
            // Validation
            $nom = $request->request->get('nomProduit');
            $prix = $request->request->get('prixProduit');
            $imageAvant = $request->files->get('imageProduitAvant');
            $imageApres = $request->files->get('imageProduitApres');

            if (empty($nom) || empty($prix)) {
                $this->addFlash('error', 'Le nom et le prix sont obligatoires');
                return $this->redirectToRoute('modifier_produit', ['id' => $id]);
            }

            // Mise à jour
            $produit->setNomProduit($nom);
            $produit->setPrixProduit($prix);

            // Gestion des images
            $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads';
            
            if ($imageAvant) {
                // Supprime l'ancienne image si elle existe
                $oldImage = $uploadsDir.'/'.$produit->getImageProduitAvant();
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }
                
                $newFilename = md5(uniqid()).'.'.$imageAvant->guessExtension();
                $imageAvant->move($uploadsDir, $newFilename);
                $produit->setImageProduitAvant($newFilename);
            }

            if ($imageApres) {
                // Supprime l'ancienne image si elle existe
                $oldImage = $uploadsDir.'/'.$produit->getImageProduitApres();
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }
                
                $newFilename = md5(uniqid()).'.'.$imageApres->guessExtension();
                $imageApres->move($uploadsDir, $newFilename);
                $produit->setImageProduitApres($newFilename);
            }

            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('produits_admin');
        }

        return $this->render('produitsAdminModifier.html.twig', [
            'produit' => $produit
        ]);
    }

   #[Route('/produitsAdminSupprimer/{id}', name: 'supprimer_produit')]
    public function supprimerProduit(int $id, EntityManagerInterface $entityManager)
    {
        $produit = $entityManager->getRepository(Produits::class)->find($id);
        
        if (!$produit) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('produits_admin');
        }

        // Suppression des fichiers images
        $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads';
        
        $imageAvant = $uploadsDir.'/'.$produit->getImageProduitAvant();
        if (file_exists($imageAvant)) {
            unlink($imageAvant);
        }
        
        $imageApres = $uploadsDir.'/'.$produit->getImageProduitApres();
        if (file_exists($imageApres)) {
            unlink($imageApres);
        }

        $entityManager->remove($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé avec succès !');
        return $this->redirectToRoute('produits_admin');
    }
}
