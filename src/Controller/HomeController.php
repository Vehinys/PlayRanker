<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\CommentRepository; // Ajoutez cette ligne
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/accueil', name: 'home')]
    public function home(

        Request $request, 
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository 

    ): Response {

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        // Récupérer les commentaires
        $comments = $commentRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contact);
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('home');
        }
    
        return $this->render('pages/home/index.html.twig', [
            'form' => $form->createView(),
            'comments' => $comments 
        ]);
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('home');
    }
}