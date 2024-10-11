<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CrudCommentController extends AbstractController
{
    #[Route('/new', name: 'comment_new', methods: ['GET', 'POST'])]
    public function new(
        
        Request $request, 
        EntityManagerInterface $entityManager
        
    ): Response {

        $comments = new Comment();
        $form = $this->createForm(CommentType::class, $comments);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comments->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($comments);
            $entityManager->flush();
        
            return $this->redirectToRoute('jeux');
        }

        return $this->render('pages/jeux/crudComment/newComment.html.twig', [
            'comments' => $comments,
            'form' => $form->createView(),
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