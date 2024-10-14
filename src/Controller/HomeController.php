<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Contact;
use App\Form\ContactType;
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
        EntityManagerInterface $entityManager

    ): Response {
    
        $form = $this->createForm(ContactType::class); // Associe l'objet Contact au formulaire
        $form->handleRequest($request);

        // dd($_POST);
    
        // if ($form->isSubmitted() && $form->isValid()) {
        //     // Les données du formulaire sont déjà associées à l'objet $contact
        //     $entityManager->persist($contact);
        //     $entityManager->flush();
    
        //     $this->addFlash('success', 'Votre message a été envoyé avec succès !');
        //     return $this->redirectToRoute('home');
        // }
    
        return $this->render('pages/home/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }

    #[Route('/accueil/contact', name: 'contact')]
    public function contact(

        Request $request, 
        EntityManagerInterface $entityManager

    ): Response {
    
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact); // Associe l'objet Contact au formulaire
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            
           

            $entityManager->persist($contact);
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('home');
        }
        $this->addFlash('error', 'Sorry, a problem occured !');
        return $this->redirectToRoute('home');
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('home');
    }
}