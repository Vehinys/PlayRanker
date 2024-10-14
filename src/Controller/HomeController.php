<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\CommentRepository;
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
        CommentRepository $commentRepository

    ): Response {
    
        $form = $this->createForm(ContactType::class); 
        $form->handleRequest($request);

        $comments = $commentRepository->findAll();
    
        return $this->render('pages/home/index.html.twig', [
            'contactForm' => $form->createView(),
            'comments' => $comments,
        ]);
    }

    #[Route('/accueil/contact', name: 'contact')]
    public function contact(

        Request $request, 
        EntityManagerInterface $entityManager

    ): Response {
        
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contact);
            $entityManager->flush();
    
            $this->addFlash('success', 'Your message has been sent successfully!');
            return $this->redirectToRoute('home');
        }
    
        // Si le formulaire n'est pas soumis ou n'est pas valide, on redirige vers la page d'accueil
        $this->addFlash('error', 'Sorry, a problem occurred!');
        return $this->redirectToRoute('home');
    }
    


    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('home');
    }
}