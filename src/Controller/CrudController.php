<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Form\TopicType;
use App\Form\EditTopicType;
use App\Entity\CategoryForum;
use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategoryForumRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CrudController extends AbstractController
{
    // -------------------------------------------------------------------------
    // Création d'un nouveau topic
    // -------------------------------------------------------------------------

    #[Route('/forum/category/{categoryId}/topic/new', name: 'topic_new')]
    public function newTopic(
        string $categoryId,
        CategoryForumRepository $categoryForumRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $categoryId = (int)$categoryId;
    
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        // Récupérer la catégorie
        $categoryForum = $em->getRepository(CategoryForum::class)->find($categoryId);
        if (!$categoryForum) {
            throw $this->createNotFoundException('Catégorie non trouvée.');
        }
    
        // Créer un nouveau topic
        $topic = new Topic();
        $topic->setCategoryForum($categoryForum)
              ->setCreatedAt(new \DateTimeImmutable())
              ->setUser($user);
    
        // Créer le formulaire
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $postContent = $form->get('post')->getData();
    
            // Créer un nouveau post
            $post = new Post();
            $post->setContent($postContent)
                 ->setCreatedAt(new \DateTimeImmutable())
                 ->setTopic($topic)
                 ->setUser($user);
    
            $topic->addPost($post);
    
            $em->persist($topic);
            $em->persist($post);  
            $em->flush();
    
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
    public function editTopic(Request $request, Topic $topic, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EditTopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); 

            return $this->redirectToRoute('topic', ['categoryId' => $topic->getCategoryForum()->getId()]);
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
    public function deleteTopic(Request $request, Topic $topic, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->request->get('_token'))) {

            $em->remove($topic);
            $em->flush();
        }

        return $this->redirectToRoute('forum');
    }

    // -------------------------------------------------------------------------
    // Création d'un nouveau post
    // -------------------------------------------------------------------------

    #[Route('/forum/new-post/{topicId}/{categoryId}', name: 'new_post')]
    public function newPost(
        Request $request, 
        EntityManagerInterface $em, 
        TopicRepository $topicRepository, 
        string $topicId, 
        string $categoryId
    ): Response {

        $topic = $topicRepository->find($topicId);
        if (!$topic) {
            throw $this->createNotFoundException('Le topic avec l\'id ' . $topicId . ' n\'existe pas.');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new \DateTimeImmutable())
                 ->setTopic($topic)
                 ->setUser($this->getUser());

            $em->persist($post);
            $em->flush();

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
    public function editPost(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $categoryId = $post->getTopic()->getCategoryForum()->getId();
            
            return $this->redirectToRoute('topic', [
                'id' => $post->getTopic()->getId(),
                'categoryId' => $categoryId, 
            ]);
        }

        return $this->render('pages/forum/crud/editPost.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    // -------------------------------------------------------------------------
    // Suppression d'un post
    // -------------------------------------------------------------------------

    #[Route('/forum/post/delete/{id}', name: 'delete_post')]
    public function deletePost(
        int $id,
        PostRepository $postRepository, 
        EntityManagerInterface $em
    ): Response {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé.');
        }

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('forum');
    }
}
