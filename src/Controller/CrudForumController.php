<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Form\TopicType;
use App\Form\EditTopicType;
use App\Entity\CategoryForum;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CrudForumController extends AbstractController
{
    // -------------------------------------------------------------------------
    // Création d'un nouveau topic
    // -------------------------------------------------------------------------

    #[Route('/forum/category/{categoryId}/topic/new', name: 'topic_new')]
    public function newTopic(

        string $categoryId, 
        Request $request,
        EntityManagerInterface $manager

    ): Response {

        $categoryId = (int)$categoryId;
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $categoryForum = $manager->getRepository(CategoryForum::class)->find($categoryId);

        if (!$categoryForum) {
            throw $this->createNotFoundException('Catégorie non trouvée.'); // Gestion des erreurs
        }

        $topic = new Topic();
        $topic->setCategoryForum($categoryForum);
        $topic->setCreatedAt(new \DateTimeImmutable());
        $topic->setUser($user);
        
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postContent = $form->get('post')->getData();
            $post = new Post();
            $post->setContent($postContent);
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setTopic($topic);
            $post->setUser($user);
            $topic->addPost($post);

            $manager->persist($topic);
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('topic', [
                'categoryId' => $categoryForum->getId(),
                'id' => $topic->getId(),
            ]);
        }

        return $this->render('pages/forum/crud/newTopic.html.twig', [
            'form' => $form->createView(),
            'categoryForum' => $categoryForum,
        ]);
    }

    // -------------------------------------------------------------------------
    // Édition d'un topic existant
    // -------------------------------------------------------------------------

    #[Route('/topic/{id}/edit', name: 'topic_edit')]
    public function editTopic(

        Request $request, 
        Topic $topic, 
        EntityManagerInterface $manager

    ): Response {

        $form = $this->createForm(EditTopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            return $this->redirectToRoute('topic', [
                'categoryId' => $topic->getCategoryForum()->getId(),
            ]);
        }

        return $this->render('pages/forum/crud/editTopic.html.twig', [
            'form' => $form->createView(),
            'topic' => $topic,
        ]);
    }

    // -------------------------------------------------------------------------
    // Suppression d'un topic
    // -------------------------------------------------------------------------

    #[Route('/forum/topic/delete/{id}', name: 'topic_delete', methods: ['POST'])]
    public function deleteTopic(

        Topic $topic, 
        Request $request, 
        EntityManagerInterface $manager

    ): Response {

        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->request->get('_token'))) {
            $manager->remove($topic);
            $manager->flush(); 
        }

        return $this->redirectToRoute('forum');
    }

    // -------------------------------------------------------------------------
    // Création d'un nouveau post
    // -------------------------------------------------------------------------

    #[Route('/forum/new-post/{topicId}/{categoryId}', name: 'new_post')]
    public function newPost(

        string $topicId, 
        Request $request, 
        string $categoryId,
        EntityManagerInterface $manager, 
        TopicRepository $topicRepository, 

    ): Response {

        $topic = $topicRepository->find($topicId);

        if (!$topic) {
            throw $this->createNotFoundException('Le topic avec l\'id ' . $topicId . ' n\'existe pas.');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setTopic($topic);
            $post->setUser($this->getUser());
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('topic', [
                'categoryId' => $categoryId,
                'id' => $topicId
            ]);
        }

        return $this->render('pages/forum/crud/newPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Édition d'un post existant
    // -------------------------------------------------------------------------

    #[Route('/forum/post/{id}/edit', name: 'post_edit')]
    public function editPost(

        Request $request, 
        Post $post, 
        EntityManagerInterface $manager

    ): Response {
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->flush();
            return $this->redirectToRoute('topic', [

                'categoryId' => $post->getTopic()->getCategoryForum()->getId(),
                'id' => $post->getTopic()->getId(),

            ]);
        }

        return $this->render('pages/forum/crud/editPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Suppression d'un post
    // -------------------------------------------------------------------------

    #[Route('/forum/post/{id}/delete', name: 'post_delete', methods: ['POST'])]
    public function deletePost(

        Request $request, 
        Post $post, 
        EntityManagerInterface $manager

    ): Response {

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $manager->remove($post);
            $manager->flush();

        return $this->redirectToRoute('topic', [
            'categoryId' => $post->getTopic()->getCategoryForum()->getId(),
            'id' => $post->getTopic()->getId(),
        ]);

        }
    }
}