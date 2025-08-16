<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeStatutType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandesController extends AbstractController
{
   #[Route('/commandesAdmin', name: 'commandes_admin', methods: ['GET', 'POST'])]
public function index(Request $request, CommandeRepository $commandeRepository, EntityManagerInterface $em): Response
{
    // Gestion du changement de statut
    if ($request->isMethod('POST')) {
        $commande = $commandeRepository->find($request->request->get('commande_id'));
        
        if ($commande && $newStatut = $request->request->get('statut')) {
            try {
                $commande->setStatut($newStatut);
                $em->flush();
                $this->addFlash('success', 'Statut modifié avec succès!');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Statut invalide');
            }
            return $this->redirectToRoute('commandes_admin');
        }
    }

    // récupère selectedUserId depuis GET (ex: ?selectedUserId=3)
    $selectedUserId = $request->query->get('selectedUserId');

    // Envoi des données à Twig
    return $this->render('commandesAdmin.html.twig', [
        'commandes' => $commandeRepository->findAll(),
        'selectedUserId' => $selectedUserId,
    ]);
}



    
}