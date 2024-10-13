<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CrudCommentController extends AbstractController
{
    #[Route('/new/{id}', name: 'comment_new', methods: ['GET', 'POST'])]
    public function new(
        int $id, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        GameRepository $gameRepository,
        CommentRepository $commentRepository
    ): Response {
        // Récupérer le jeu à partir de son ID
        $game = $gameRepository->find($id); // Corrigé pour éviter le conflit de variable
    
        if (!$game) {
            throw $this->createNotFoundException('Game not found');
        }
    
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        // Récupérer les commentaires associés au jeu
        $comments = $commentRepository->findBy(['game' => $game]);
    
        // Créer un nouveau commentaire
        $comment = new Comment(); // Renommé en $comment pour éviter les conflits
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
    
        // Associer le commentaire au jeu et à l'utilisateur
        $comment->setUser($user);
        $comment->setGame($game);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($comment);
            $entityManager->flush();
    
            return $this->redirectToRoute('jeux');
        }
    
        return $this->render('pages/jeux/crudComment/newComment.html.twig', [
            'comments' => $comments,  // Commentaires existants
            'gameDetail' => $game,    // Détail du jeu
            'form' => $form->createView()
        ]);
    }
    
    

    // ----------------------------------------------------------------------------------- //

    #[Route('/{id}/edit', name: 'comment_edit', methods: ['GET', 'POST'])]
    public function edit(
        
        Request $request, 
        Comment $comment, 
        EntityManagerInterface $entityManager
        
    ): Response {

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
        
            return $this->redirectToRoute('jeux');
        }

        return $this->render('pages/jeux/crudComment/editComment.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    // ----------------------------------------------------------------------------------- //

    #[Route('/{id}', name: 'comment_delete', methods: ['POST'])]
    public function delete(
        
    Request $request, 
    Comment $comment, 
    EntityManagerInterface $entityManager
    
    ): Response {

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        
            return $this->redirectToRoute('jeux');
        }
    }
}    