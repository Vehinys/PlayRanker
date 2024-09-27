<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\TopicFormType;
use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategoryForumRepository;
use Symfony\Component\HttpFoundation\Request;
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

        $topic = $topicRepository ->find($id);
        $categories = $categoryForumRepository -> findBy([ ],['name' => 'ASC']);
        $posts = $postRepository -> findBy(['topic' => $topic], ['createdAt' => 'DESC']);

        return $this->render('pages/forum/post.html.twig', [
            'posts' => $posts,
            'categories'=> $categories
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/forum/topics/post/create/{id}', name: 'post_create')]
    public function createPost (
            
            Topic $topic,
            Post $post,
            Request $request,
            EntityManagerInterface $manager,

        ): Response {

            $topic = new Topic();
            $post  = new Post();

            $form = $this->createForm(TopicFormType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $topic = $form->getData();
                $post = $form->getData();

                $manager -> persist($topic);
                $manager -> persist($post);
                $manager -> flush();

                return $this->redirectToRoute('post');
        }
            return $this->render('pages/forum/topics/postNew.html.twig', [
                'form' => $form,
        ]);
    }
}
