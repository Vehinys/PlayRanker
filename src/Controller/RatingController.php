<?php

namespace App\Controller;

use App\Repository\ScoreRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class RatingController extends AbstractController
{
    #[Route('/jeux/{id}/rating', name: 'rating')]
    public function viewRating(

        int $id,
        ScoreRepository $scoreRepository,

    ): Response {

        $user = $this->getUser();

        $scores = $scoreRepository->findBy(['id'=> $id] , ['user' => $user]);

        return $this->render('pages/jeux/detail.html.twig', [
            'Scores' => $scores

        ]);
    }
}