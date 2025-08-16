<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactsController extends AbstractController
{
    #[Route('/contactsAdmin', name: 'contacts_admin')]
    public function index(Request $request, ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findAll(); 

        $selectedUserId = $request->query->get('selectedUserId');

        return $this->render('contactsAdmin.html.twig', [
            'contacts' => $contacts,
            'selectedUserId' => $selectedUserId,
        ]);
    }
}
