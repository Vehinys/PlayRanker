<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Form\TopicType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CrudController extends AbstractController
{
    #[Route('/forum/topic/new', name: 'topic_new')]
    public function newTopic(
        
        Request $request, 
        EntityManagerInterface $em
        
    ): Response {
        $topic = new Topic();
        $topic->setCreatedAt(new \DateTimeImmutable());
        
        // Créer un post associé au topic
        $post = new Post();
        $post->setContent("Votre premier message dans ce topic.")
                ->setCreatedAt(new \DateTimeImmutable())
                ->setTopic($topic)
                ->setUser($this->getUser());

        $topic->addPost($post); // Ajoute le post au topic

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($topic); // Persiste le topic
            $em->persist($post);   // Persiste le post
            $em->flush();

            return $this->redirectToRoute('topic');
        }

        return $this->render('pages/forum/crud/newTopic.html.twig', [
            'form' => $form->createView(),
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/topic/{id}/edit', name: 'topic_edit')]
    public function editTopic(Request $request, Topic $topic, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); // Met à jour le topic

            return $this->redirectToRoute('topic');
        }

        return $this->render('pages/forum/crud/editTopic.html.twig', [
            'form' => $form->createView(),
            'topic' => $topic,
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/topic/delete/{id}', name: 'topic_delete', methods: ['POST'])]
    public function deleteTopic(Request $request, Topic $topic, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->request->get('_token'))) {
            // Suppression du topic, les posts sont supprimés grâce à orphanRemoval=true
            $em->remove($topic);
            $em->flush();
        }

        return $this->redirectToRoute('topic_index');
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/post/new/{topicId}', name: 'post_new')]
    public function newPost(Request $request, EntityManagerInterface $em, int $topicId): Response
    {
        $post = new Post();
        $post->setCreatedAt(new \DateTimeImmutable())
            ->setTopic($em->getRepository(Topic::class)->find($topicId))
            ->setUser($this->getUser()); // Assurez-vous que l'utilisateur est authentifié

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('topic', ['id' => $topicId]); // Redirigez vers le topic
        }

        return $this->render('pages/forum/crud/newPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/forum/post/{id}/edit', name: 'post_edit')]
    public function editPost(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); // Met à jour le post

            return $this->redirectToRoute('topic', ['id' => $post->getTopic()->getId()]);
        }

        return $this->render('pages/forum/crud/editPost.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/forum/post/post/{id}', name: 'post_delete', methods: ['POST'])]
    public function deletePost(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('topic', ['id' => $post->getTopic()->getId()]);
    }
}

