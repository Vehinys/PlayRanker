<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentController extends AbstractController
{
    #[Route('/forum', name: 'forum')]
    public function index(

    CommentRepository $commentRepository,

    ): Response {

        $comments = $commentRepository->findAll();

        return $this->render('pages/jeux/detail.html.twig', [
            'comments' => $comments

        ]);
    }

}
