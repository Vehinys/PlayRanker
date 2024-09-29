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
        // Convertir $categoryId en entier
        $categoryId = (int)$categoryId;
    
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login'); // Rediriger si non connecté
        }
    
        // Récupérer la catégorie
        $categoryForum = $em->getRepository(CategoryForum::class)->find($categoryId);
        if (!$categoryForum) {
            throw $this->createNotFoundException('Catégorie non trouvée.');
        }
    
        // Créer un nouveau topic
        $topic = new Topic();
        $topic->setCategoryForum($categoryForum) // Associer la catégorie
              ->setCreatedAt(new \DateTimeImmutable())
              ->setUser($user); // Associer l'utilisateur au topic
    
        // Créer le formulaire
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le contenu du premier message
            $postContent = $form->get('post')->getData();
    
            // Créer un nouveau post
            $post = new Post();
            $post->setContent($postContent)
                 ->setCreatedAt(new \DateTimeImmutable())
                 ->setTopic($topic)
                 ->setUser($user); // Associer l'utilisateur ici
    
            $topic->addPost($post); // Ajoute le post au topic
    
            // Sauvegarder le topic et le post
            $em->persist($topic); // Persist le topic
            $em->persist($post);  // Persist le post
            $em->flush();
    
            // Redirection vers le topic, en ajoutant categoryId et id
            return $this->redirectToRoute('topic', [
                'categoryId' => $categoryForum->getId(), // ID de la catégorie
                'id' => $topic->getId(), // ID du topic
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
            $em->flush(); // Met à jour le topic

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
            // Suppression du topic, les posts sont supprimés grâce à orphanRemoval=true
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
        // Récupération du topic
        $topic = $topicRepository->find($topicId);
        if (!$topic) {
            throw $this->createNotFoundException('Le topic avec l\'id ' . $topicId . ' n\'existe pas.');
        }

        // Création d'un nouveau post
        $post = new Post();
        $form = $this->createForm(PostType::class, $post); // Corrigé pour utiliser $post
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new \DateTimeImmutable()) // Associer la date de création
                 ->setTopic($topic) // Associer le post au topic
                 ->setUser($this->getUser()); // Associer l'utilisateur ici

            $em->persist($post);
            $em->flush();

            // Redirection vers le topic en fournissant categoryId et topicId
            return $this->redirectToRoute('topic', [
                'categoryId' => $categoryId, // L'ID de la catégorie
                'id' => $topicId // L'ID du topic
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
            $em->flush(); // Met à jour le post

            // Récupérer le categoryId à partir du topic du post
            $categoryId = $post->getTopic()->getCategoryForum()->getId();
            
            return $this->redirectToRoute('topic', [
                'id' => $post->getTopic()->getId(),
                'categoryId' => $categoryId, // Ajout du categoryId ici
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

        return $this->redirectToRoute('forum'); // Redirigez vers une route appropriée après la suppression
    }
}
