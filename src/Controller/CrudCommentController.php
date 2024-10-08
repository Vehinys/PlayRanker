<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CrudCommentController extends AbstractController
{
    #[Route('/crud/comment', name: 'app_crud_comment')]
    public function index(): Response
    {
        return $this->render('crud_comment/index.html.twig', [
            'controller_name' => 'CrudCommentController',
        ]);
    }
}
