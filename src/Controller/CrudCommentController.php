<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Comment;
use App\Form\CommentType;
use App\HttpClient\ApiHttpClient;
use App\Repository\GameRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CrudCommentController extends AbstractController
{
    #[Route('/new/{id}', name: 'comment_new', methods: ['GET', 'POST'])]
    public function new(
        
        int $id, // ID provenant de l'API RAWG
        Request $request,
        EntityManagerInterface $entityManager,
        ApiHttpClient $apiHttpClient,
        CommentRepository $commentRepository,
        GameRepository $gameRepository

    ): Response {
        
        // Récupérer les détails du jeu à partir de l'API RAWG
        $gameData = $apiHttpClient->gameDetail($id);
    
        if (!$gameData) {
            throw $this->createNotFoundException('Game not found in the API');
        }
    
        // Vérifier si le jeu existe déjà dans la base de données
        $game = $gameRepository->findOneBy(['id_game_api' => $id]);
    
        // Si le jeu n'existe pas dans la base de données, l'ajouter
        if (!$game) {
            $game = new Game();
            $game->setIdGameApi($id);
            $game->setName($gameData['name']);
            $game->setData($gameData); 
    
            $entityManager->persist($game);
            $entityManager->flush();
        }
    
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        // Récupérer les commentaires associés au jeu dans la base de données
        $comments = $commentRepository->findBy(['game' => $game]);
    
        // Créer un nouveau commentaire
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            // Associer le commentaire au jeu et à l'utilisateur
            $comment->setUser($user);
            $comment->setGame($game);
            $comment->setCreatedAt(new \DateTimeImmutable());
    
            // Persister le commentaire dans la base de données
            $entityManager->persist($comment);
            $entityManager->flush();
    
            // Rediriger vers la page du jeu en passant l'ID du jeu
            return $this->redirectToRoute('detail_jeu', [
                'id' => $id
            ]);
        }
    
        return $this->render('pages/jeux/crudComment/newComment.html.twig', [
            'comments' => $comments,  // Commentaires existants
            'gameDetail' => $gameData,  // Détails du jeu provenant de l'API
            'form' => $form->createView()
        ]);
    }
    

    // ----------------------------------------------------------------------------------- //

    #[Route('/{id}/edit', name: 'comment_edit', methods: ['GET', 'POST'])]
    public function edit(

        int $id,
        Request $request, 
        CommentRepository $commentRepository,
        Comment $comment, 
        EntityManagerInterface $entityManager
        
    ): Response {

        $comment = $commentRepository->find($id);
    
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('detail_jeu', [
                'id' => $comment->getGame()->getIdGameApi()
            ]);
        }

        return $this->render('pages/jeux/crudComment/editComment.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    // ----------------------------------------------------------------------------------- //

    #[Route('/{id}', name: 'comment_delete', methods: ['POST'])]
    public function delete(
    
        int $id,
        Request $request, 
        Comment $comment, 
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManager
    
    ): Response {

        $comment = $commentRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        
            return $this->redirectToRoute('detail_jeu', [
                'id' => $comment->getGame()->getIdGameApi()
            ]);
        }
    }
}    