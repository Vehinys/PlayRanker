<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\ScoreRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/accueil', name: 'home', methods: ['GET', 'POST'])]
    public function home(

        Request $request, 
        CommentRepository $commentRepository,
        ScoreRepository $scoreRepository
        
    ): Response {
        $form = $this->createForm(ContactType::class); 
        $form->handleRequest($request);
    
        $comments = $commentRepository->findAll();
    
        // Récupérer les jeux avec les meilleures notes
        $topGames = $scoreRepository->findTopGames(3);
    
        return $this->render('pages/home/index.html.twig', [
            'contactForm' => $form->createView(),
            'comments' => $comments,
            'topGames' => $topGames,

        ]);
    }

    #[Route('/accueil/contact', name: 'contact')]
    public function contact(

        Request $request, 
        MailerInterface $mailer,
        CommentRepository $commentRepository,
        ScoreRepository $scoreRepository
        
    ): Response {

        $comments = $commentRepository->findAll();
        $topGames = $scoreRepository->findTopGames(3);

        $contactForm = $this->createForm(ContactType::class);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $data = $contactForm->getData();

            $adress = $data['email'];
            $subject = $data['subject'];
            $content = $data['content'];

            $email = (new Email());
            $email  ->from($adress)
                    ->to('albert.lecomte1989@gmail.com')
                    ->subject($subject)
                    ->text($content);

            $mailer->send($email);

            return $this->redirectToRoute('home');
        }

        return $this->render('pages/home/index.html.twig', [
            'contactForm' => $contactForm->createView(),
            'comments' => $comments,
            'topGames' => $topGames,
        ]);
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('home');
    }
}