<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Repository\CategoryForumRepository;
use App\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'forum')]
    public function index (

        CategoryForumRepository $repository,

    ): Response {

        $category = $repository -> findBy([ ],['name' => 'ASC']);

        return $this->render('pages/forum/index.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/forum/topic/{id}', name: 'forum_topic')]
    public function listTopic (

        TopicRepository $repository,
        ?Topic $topic,

    ): Response {

        $topic = $repository -> findTopicsByCategory($categoryId);

        return $this->render('pages/forum/topic.html.twig', [
            'topic' => $topic,
        ]);
    }

}
