<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\GameRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/crud/comment')]
class CrudCommentController extends AbstractController
{
    #[Route('/{gameId}', name: 'comment_index', methods: ['GET'])]
    public function index(
        
        int $gameId, 
        CommentRepository $commentRepository
        
    ): Response {

        $comments = $commentRepository->findBy(['game' => $gameId]);

        return $this->render('crud_comment/index.html.twig', [
            'comments' => $comments,
            'gameId' => $gameId,
        ]);
    }

    #[Route('/new/{gameId}', name: 'comment_new', methods: ['GET', 'POST'])]
    public function new(

        Request $request,
        EntityManagerInterface $entityManager,
        GameRepository $gameRepository,
        int $gameId

    ): Response {
        $comment = new Comment();
        $game = $gameRepository->find($gameId);
        
        if (!$game) {
            throw $this->createNotFoundException('Game not found');
        }
    
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setGame($game);
            $entityManager->persist($comment);
            $entityManager->flush();
    
            return $this->redirectToRoute('comment_index', [
                'gameId' => $gameId]);
        }
    
        return $this->render('pages/jeux/crudComment/newComment.html.twig', [
            'form' => $form,
            'gameId' => $gameId,
        ]);
    }
    

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

            return $this->redirectToRoute('comment_index', ['gameId' => $comment->getGame()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/jeux/crudComment/editComment.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'comment_delete', methods: ['POST'])]
    public function delete(
        
        Request $request, 
        Comment $comment, 
        EntityManagerInterface $entityManager
        
        ): Response {

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index', [
            
            'gameId' => $comment->getGame()
    
        ]);
    }
}
