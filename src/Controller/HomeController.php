<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // ---------------------------------------------------------- //
    // Affiche la page d'accueil
    // ---------------------------------------------------------- //
    
    #[Route('/accueil', name: 'home')]
    public function home(): Response
    {
        return $this->render('pages/home/index.html.twig', [
            'controller_name' => 'HomeController',
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
