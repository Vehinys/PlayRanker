<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use App\Repository\CategoryRepository;
use App\Repository\CategoryForumRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'forum')]
    public function index (

        CategoryForumRepository $categoryForumRepository, // Injection du repository pour gérer les catégories de forum
        TopicRepository $topicRepository, // Injection du repository pour gérer les topics
        
    ): Response { // La méthode retourne un objet Response
    
        // Récupération de toutes les catégories de forum, triées par nom de manière croissante (ASC)
        $categories = $categoryForumRepository->findBy([], ['name' => 'ASC']);
        $topics = [];

        // Récupération des 6 derniers topics, triés par date de création de manière décroissante (DESC)
        $topics = $topicRepository->findBy([], ['createdAt' => 'DESC'], 6);
    
        // Rendu de la vue 'index.html.twig' et passage des données (catégories et topics) à cette vue
        return $this->render('pages/forum/index.html.twig', [
            'categories' => $categories, // Les catégories sont passées à la vue pour affichage
            'topics' => $topics, // Les topics sont passés à la vue pour affichage
        ]);
    }
    

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

#[Route('/forum/topics/{categoryId}', name: 'topic')]
public function findTopicByCategoryForum(

    string $categoryId,
    CategoryRepository $categoryForumRepository,
    TopicRepository $topicRepository,
    PaginatorInterface $paginatorInterface,
    Request $request

): Response {
    // Récupération de la catégorie de forum
    $categoryForum = $categoryForumRepository->find($categoryId);

    // Vérifiez si la catégorie existe
    if (!$categoryForum) {
        throw $this->createNotFoundException('Catégorie de forum non trouvée.');
    }

    // Récupération de toutes les catégories de forum, triées par nom
    $categories = $categoryForumRepository->findBy([], ['name' => 'ASC']);

    // Récupération des topics associés à la catégorie de forum
    $topics = $topicRepository->findBy(['categoryForum' => $categoryForum], ['createdAt' => 'DESC']);

    $topics = $paginatorInterface->paginate(
        $topics,
        $request->query->getint('page', 1),
        12
    );

    // Initialisation d'un tableau pour stocker les posts
    $posts = [];

    // Récupération des posts de chaque topic
    foreach ($topics as $topic) {
        $posts[$topic->getId()] = $topic->getPosts(); // On associe les posts à leur topic respectif
    }

    // Rendu de la vue 'topic.html.twig'
    return $this->render('pages/forum/topic.html.twig', [
        'topics' => $topics,
        'categories' => $categories,
        'category' => $categoryForum,
        'posts' => $posts,
        'topic' => $topic,
    ]);
}

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

#[Route('/forum/topics/post/{id}', name: 'post')]
public function findPostByTopic(

    CategoryRepository $categoryForumRepository,
    TopicRepository $topicRepository,
    PostRepository $postRepository,
    int $id

): Response {

    // Récupération du topic correspondant à l'ID donné
    $topic = $topicRepository->findOneBy(['id' => $id]);
    
    
    // Vérifier si le topic existe
    // if (!$topic) {
    //     throw $this->createNotFoundException('Le post avec l\'id ' . $id . ' n\'existe pas.');
    // }

    // Récupérer la catégorie du topic
    $category = $topic->getCategoryForum();
    
    // Récupération de toutes les catégories de forum
    $categories = $categoryForumRepository->findBy([], ['name' => 'ASC']);
    
    // Récupération des posts associés au topic, triés par date de création de manière décroissante (DESC)
    $posts = $postRepository->findBy(['topic' => $topic], ['createdAt' => 'DESC'],12);
    
    // Récupérer les topics associés à la catégorie
    $topics = $topicRepository->findBy(['categoryForum' => $category], ['createdAt' => 'DESC']);
    
    // Rendu de la vue 'post.html.twig'
    return $this->render('pages/forum/post.html.twig', [
        'posts' => $posts,
        'categories' => $categories,
        'topic' => $topic,
        'category' => $category,
        'topics' => $topics
    ]);
}

/* ----------------------------------------------------------------------------------------------------------------------------------------- */



}
