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

        CategoryForumRepository $categoryForumRepository,

    ): Response {

        $categories = $categoryForumRepository -> findBy([ ],['name' => 'ASC']);

        return $this->render('pages/forum/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/forum/topics/{id}', name: 'topic')]
    public function findTopicByCategoryForum (

        CategoryRepository $categoryForumRepository,
        TopicRepository $topicRepository,
        String $id

    ): Response {

        $categoryForum = $categoryForumRepository ->find($id);
        $categories = $categoryForumRepository -> findBy([ ],['name' => 'ASC']);
        $topics = $topicRepository -> findBy(['categoryForum' => $categoryForum]);

        return $this->render('pages/forum/topic.html.twig', [
            'topics' => $topics,
            'categories'=> $categories
        ]);
    }

    #[Route('/forum/topics/post/{id}', name: 'post')]
    public function findPostByTopic (

        CategoryRepository $categoryForumRepository,
        TopicRepository $topicRepository,
        PostRepository $postRepository,
        String $id

    ): Response {

        $topic = $topicRepository ->find($id);
        $categories = $categoryForumRepository -> findBy([ ],['name' => 'ASC']);
        $posts = $postRepository -> findBy(['topic' => $topic]);

        return $this->render('pages/forum/post.html.twig', [
            'posts' => $posts,
            'categories'=> $categories
        ]);
    }

}
