<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use App\Repository\CategoryRepository;
use App\Repository\CategoryForumRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'forum')]
    public function index (

        CategoryForumRepository $categoryForumRepository, // Injection du repository pour gérer les catégories de forum
        TopicRepository $topicRepository, // Injection du repository pour gérer les topics
        
    ): Response { // La méthode retourne un objet Response
    
        // Récupération de toutes les catégories de forum, triées par nom de manière croissante (ASC)
        $categories = $categoryForumRepository->findBy([], ['name' => 'ASC']);
        
        // Récupération des 6 derniers topics, triés par date de création de manière décroissante (DESC)
        $topics = $topicRepository->findBy([], ['createdAt' => 'DESC'], 6);
    
        // Rendu de la vue 'index.html.twig' et passage des données (catégories et topics) à cette vue
        return $this->render('pages/forum/index.html.twig', [
            'categories' => $categories, // Les catégories sont passées à la vue pour affichage
            'topics' => $topics, // Les topics sont passés à la vue pour affichage
        ]);
    }
    

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/topics/{id}', name: 'topic')]
    public function findTopicByCategoryForum (

        String $id, // Paramètre dynamique représentant l'ID de la catégorie de forum
        CategoryRepository $categoryForumRepository, // Injection du repository pour gérer les catégories de forum
        TopicRepository $topicRepository // Injection du repository pour gérer les topics

    ): Response { // La méthode retourne un objet Response

        // Récupération de la catégorie de forum correspondant à l'ID donné
        $categoryForum = $categoryForumRepository->find($id);
        
        // Récupération de toutes les catégories de forum, triées par nom de manière croissante (ASC)
        $categories = $categoryForumRepository->findBy([], ['name' => 'ASC']);
        
        // Récupération des topics associés à la catégorie de forum, triés par date de création de manière décroissante (DESC)
        $topics = $topicRepository->findBy(['categoryForum' => $categoryForum], ['createdAt' => 'DESC']);

        // Rendu de la vue 'topic.html.twig' et passage des données (topics et catégories) à cette vue
        return $this->render('pages/forum/topic.html.twig', [
            'topics' => $topics, // Les topics sont passés à la vue pour affichage
            'categories' => $categories // Les catégories sont passées à la vue pour affichage
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/topics/post/{id}', name: 'post')] 
    public function findPostByTopic (

        CategoryRepository $categoryForumRepository, // Injection du repository pour gérer les catégories de forum
        TopicRepository $topicRepository, // Injection du repository pour gérer les topics
        PostRepository $postRepository, // Injection du repository pour gérer les posts
        String $id // Paramètre dynamique représentant l'ID du topic
    ): Response { // La méthode retourne un objet Response

        // Récupération du topic correspondant à l'ID donné
        $topics = $topicRepository->find($id);
        
        // Récupération de toutes les catégories de forum, triées par nom de manière croissante (ASC)
        $categories = $categoryForumRepository->findBy([], ['name' => 'ASC']);
        
        // Récupération des posts associés au topic, triés par date de création de manière décroissante (DESC)
        $posts = $postRepository->findBy(['topic' => $topics], ['createdAt' => 'DESC']);

        // Rendu de la vue 'post.html.twig' et passage des données (posts, catégories et topic) à cette vue
        return $this->render('pages/forum/post.html.twig', [
            'posts' => $posts, // Les posts sont passés à la vue pour affichage
            'categories' => $categories, // Les catégories sont passées à la vue pour affichage
            'topics' => $topics // Le topic est passé à la vue pour affichage
        ]);
    }


/* ----------------------------------------------------------------------------------------------------------------------------------------- */



}
