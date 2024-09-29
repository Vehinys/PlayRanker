<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategoryForumRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'forum')]
    public function index (

        CategoryForumRepository $categoryForumRepository,
        TopicRepository $topicRepository,
        PostRepository $postRepository,

    ): Response {

        $categories = $categoryForumRepository -> findBy([ ],['name' => 'ASC']);
        $topics = $topicRepository -> findBy([ ],['createdAt' => 'DESC'],6);
        $post = $postRepository -> findOneBy([ ],['createdAt' => 'ASC']);

        return $this->render('pages/forum/index.html.twig', [
            'categories' => $categories,
            'topics' => $topics,
            'post' => $post,
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/topics/{id}', name: 'topic')]
    public function findTopicByCategoryForum (

        CategoryRepository $categoryForumRepository,
        TopicRepository $topicRepository,
        String $id

    ): Response {

        $categoryForum = $categoryForumRepository ->find($id);
        $categories = $categoryForumRepository -> findBy([ ],['name' => 'ASC']);
        $topics = $topicRepository -> findBy(['categoryForum' => $categoryForum], ['createdAt' => 'DESC']);

        return $this->render('pages/forum/topic.html.twig', [
            'topics' => $topics,
            'categories'=> $categories
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/topics/post/{id}', name: 'post')]
    public function findPostByTopic (

        CategoryRepository $categoryForumRepository,
        TopicRepository $topicRepository,
        PostRepository $postRepository,
        String $id

    ): Response {

        $topics = $topicRepository ->find($id);
        $categories = $categoryForumRepository -> findBy([ ],['name' => 'ASC']);
        $posts = $postRepository -> findBy(['topic' => $topics], ['createdAt' => 'DESC']);

        return $this->render('pages/forum/post.html.twig', [
            'posts' => $posts,
            'categories'=> $categories,
            'topics' => $topics
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/delete/topic/{id}', name: 'delete_topic')]
    public function deleteTopic(
        int $id,
        TopicRepository $topicRepository,
        EntityManagerInterface $entityManager,
        Security $security,
        Request $request
    ): Response {
        // Recherche le topic avec l'ID
        $topic = $topicRepository->findOneBy(['id' => $id]);

        if (!$topic) {
            // Si le topic n'est pas trouvé, redirection vers le forum
            return $this->redirectToRoute('forum');
        }

        // Vérifie si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {
            // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('login');
        }

        // Vérifie si l'utilisateur est l'auteur du topic
        if ($topic->getAuthor() !== $user) {
            // Si l'utilisateur n'est pas l'auteur, redirection vers le forum
            return $this->redirectToRoute('forum');
        }

        // Supprime le topic
        $entityManager->remove($topic);
        $entityManager->flush();

        // Redirection vers le forum après suppression
        return $this->redirectToRoute('forum');
    }



/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/delete/post/{id}', name: 'delete_post')]
    public function deletePost(
        int $id,
        PostRepository $postRepository,
        EntityManagerInterface $entityManager,
        Security $security,
        Request $request
    ): Response {
        // Recherche le post avec l'ID
        $post = $postRepository->findOneBy(['id' => $id ]);

        if (!$post) {
            return $this->redirectToRoute('forum');
        }

        // Vérifie si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {

           // Redirige vers la page de connexion
            return $this->redirectToRoute('login'); 
        }

        // Vérifie si l'utilisateur est l'auteur du post
        if ($post->getAuthor() !== $user) {
            return $this->redirectToRoute('forum');
        }

        // Supprime le post
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('forum');
    }

}
