<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil')]
    public function index(

    
    ): Response {

        $user = $this->getUser();
    
        return $this->render('pages/profil/index.html.twig', [
            'user' => $user
        ]);
    }
}
