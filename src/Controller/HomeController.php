<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // ---------------------------------------------------------- //
    // Affiche la page d'accueil
    // ---------------------------------------------------------- //
    
    #[Route('/accueil', name: 'home')]
    public function home(

        CommentRepository $commentRepository

    ): Response {

        $comments = $commentRepository->findBy([], ['createdAt' => 'DESC'], 5);


        return $this->render('pages/home/index.html.twig', [
            'comments' => $comments,

        ]);
    }

    // ---------------------------------------------------------- //
    // Redirige vers la page d'accueil
    // ---------------------------------------------------------- //
    
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('home');
    }
}
