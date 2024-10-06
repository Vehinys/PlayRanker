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
    // ----------------------------------------------------------------- //
    // Affiche la liste des catégories de forum et les derniers topics
    // ----------------------------------------------------------------- //
    #[Route('/forum', name: 'forum')]
    public function index(

        CategoryForumRepository $categoryForumRepository,
        TopicRepository $topicRepository

    ): Response {

        // Récupération de toutes les catégories de forum
        $categories = $categoryForumRepository->findBy([], ['name' => 'ASC']);

        // Récupération des 6 derniers topics
        $topics = $topicRepository->findBy([], ['createdAt' => 'DESC'], 6);

        // Rendu de la vue 'index.html.twig'
        return $this->render('pages/forum/index.html.twig', [
            'categories' => $categories,
            'topics' => $topics,
        ]);
    }

    // ---------------------------------------------------------- //
    // Affiche les topics d'une catégorie de forum donnée
    // ---------------------------------------------------------- //

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

        // Vérification de l'existence de la catégorie
        if (!$categoryForum) {
            throw $this->createNotFoundException('Catégorie de forum non trouvée.');
        }

        // Récupération des topics associés à la catégorie
        $topics = $topicRepository->findBy(['categoryForum' => $categoryForum], ['createdAt' => 'DESC']);

        // Pagination des topics
        $topics = $paginatorInterface->paginate(
            $topics,
            $request->query->getInt('page', 1),
            12
        );

        // Initialisation d'un tableau pour stocker les posts
        $posts = [];
        foreach ($topics as $topic) {
            $posts[$topic->getId()] = $topic->getPosts();
        }

        // Rendu de la vue 'topic.html.twig'
        return $this->render('pages/forum/topic.html.twig', [
            'topics' => $topics,
            'categories' => $categoryForumRepository->findBy([], ['name' => 'ASC']),
            'category' => $categoryForum,
            'posts' => $posts,
        ]);
    }

    // ---------------------------------------------------------- //
    // Affiche les posts d'un topic donné
    // ---------------------------------------------------------- //

    #[Route('/forum/topics/post/{id}', name: 'post')]
    public function findPostByTopic(

        CategoryRepository $categoryForumRepository,
        PaginatorInterface $paginatorInterface,
        Request $request,
        TopicRepository $topicRepository,
        PostRepository $postRepository,
        int $id

    ): Response {

        // Récupération du topic correspondant à l'ID donné
        $topic = $topicRepository->findOneBy(['id' => $id]);

        // Vérification de l'existence du topic
        if (!$topic) {
            throw $this->createNotFoundException('Le post avec l\'id ' . $id . ' n\'existe pas.');
        }

        // Récupération de la catégorie du topic
        $category = $topic->getCategoryForum();
        
        // Récupération des posts associés au topic
        $posts = $postRepository->findBy(['topic' => $topic], ['createdAt' => 'DESC'], 12);
        $posts = $paginatorInterface->paginate(
            $posts,
            $request->query->getInt('page', 1),
            5
        );

        // Récupération des topics associés à la catégorie
        $topics = $topicRepository->findBy(['categoryForum' => $category], ['createdAt' => 'DESC']);

        // Rendu de la vue 'post.html.twig'
        return $this->render('pages/forum/post.html.twig', [
            'posts' => $posts,
            'categories' => $categoryForumRepository->findBy([], ['name' => 'ASC']),
            'topic' => $topic,
            'category' => $category,
            'topics' => $topics
        ]);
    }
}
