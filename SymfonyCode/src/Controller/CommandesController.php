<?php

namespace App\Controller;

use App\Entity\Commandes;
use App\Form\CommandeStatutType;
use App\Repository\CommandesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandesController extends AbstractController
{
   #[Route('/commandesAdmin', name: 'commandes_admin', methods: ['GET', 'POST'])]
    public function index(Request $request, CommandesRepository $commandesRepository, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $commande = $commandesRepository->find($request->request->get('commande_id'));
            
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

        return $this->render('commandesAdmin.html.twig', [
            'commandes' => $commandesRepository->findAll(),
        ]);
    }
}