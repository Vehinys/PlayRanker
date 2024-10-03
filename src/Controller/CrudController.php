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

class CrudController extends AbstractController
{
    // -------------------------------------------------------------------------
    // Création d'un nouveau topic
    // Cette méthode permet de créer un nouveau topic dans une catégorie donnée.
    // Elle vérifie si l'utilisateur est connecté, puis crée un topic et un post.
    // -------------------------------------------------------------------------

    #[Route('/forum/category/{categoryId}/topic/new', name: 'topic_new')]
    public function newTopic(

        string $categoryId, 
        Request $request,
        EntityManagerInterface $manager

    ): Response {

        // Conversion de l'ID de la catégorie en entier
        $categoryId = (int)$categoryId;
    
        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Redirection vers la page de login si l'utilisateur n'est pas connecté
        if (!$user) {
            return $this->redirectToRoute('login');
        }
    
        // Récupérer la catégorie avec l'ID donné
        $categoryForum = $manager->getRepository(CategoryForum::class)->find($categoryId);

        // Si la catégorie n'existe pas, lever une exception
        if (!$categoryForum) {
            throw $this->createNotFoundException('Catégorie non trouvée.');
        }
    
        // Créer un nouveau topic
        $topic = new Topic();
        
        // Associer le topic à la catégorie
        $topic->setCategoryForum($categoryForum);
        
        // Définir la date de création du topic
        $topic->setCreatedAt(new \DateTimeImmutable());

        // Associer l'utilisateur actuel au topic
        $topic->setUser($user);
    
        // Créer le formulaire pour le topic
        $form = $this->createForm(TopicType::class, $topic);
        
        // Traiter la requête et soumettre les données du formulaire
        $form->handleRequest($request);
    
        // Si le formulaire est soumis et validé
        if ($form->isSubmitted() && $form->isValid()) {

            // Récupérer le contenu du premier post à partir du formulaire
            $postContent = $form->get('post')->getData();
    
            // Créer un nouveau post avec le contenu récupéré
            $post = new Post();
            
            // Associer le contenu au post
            $post->setContent($postContent);
            
            // Définir la date de création du post
            $post->setCreatedAt(new \DateTimeImmutable());
            
            // Associer le post au topic
            $post->setTopic($topic);
            
            // Associer l'utilisateur au post
            $post->setUser($user);
    
            // Ajouter le post au topic
            $topic->addPost($post);
    
            // Persister le topic et le post dans la base de données
            $manager->persist($topic);
            $manager->persist($post);
            
            // Enregistrer les changements dans la base de données
            $manager->flush();
    
            // Rediriger l'utilisateur vers le topic nouvellement créé
            return $this->redirectToRoute('topic', [
                'categoryId' => $categoryForum->getId(),
                'id' => $topic->getId(),
            ]);
        }

        // Rendre la vue avec le formulaire pour créer un nouveau topic
        return $this->render('pages/forum/crud/newTopic.html.twig', [
            'form' => $form->createView(),
            'categoryForum' => $categoryForum,
        ]);
    }

    // -------------------------------------------------------------------------
    // Édition d'un topic existant
    // Cette méthode permet d'éditer un topic existant. Elle vérifie si le formulaire
    // est soumis et valide, puis met à jour les informations du topic.
    // -------------------------------------------------------------------------

    #[Route('/topic/{id}/edit', name: 'topic_edit')]
    public function editTopic(
        
        Request $request, 
        Topic $topic, 
        EntityManagerInterface $manager
        
    ): Response {

        // Créer le formulaire pour l'édition du topic
        $form = $this->createForm(EditTopicType::class, $topic);
        
        // Traiter la requête et soumettre les données du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et validé
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Enregistrer les modifications dans la base de données
            $manager->flush(); 

            // Rediriger vers le topic mis à jour
            return $this->redirectToRoute('topic', [
                'categoryId' => $topic->getCategoryForum()->getId(),
            ]);
        }

        // Rendre la vue avec le formulaire d'édition du topic
        return $this->render('pages/forum/crud/editTopic.html.twig', [
            'form' => $form->createView(),
            'topic' => $topic,
        ]);
    }

    // -------------------------------------------------------------------------
    // Suppression d'un topic
    // Cette méthode permet de supprimer un topic après validation du token CSRF.
    // -------------------------------------------------------------------------

    #[Route('/forum/topic/delete/{id}', name: 'topic_delete', methods: ['POST'])]
    public function deleteTopic(
        
        Request $request, 
        Topic $topic, 
        EntityManagerInterface $manager
        
    ): Response {
        // Vérifier la validité du token CSRF pour la suppression
        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->request->get('_token'))) {

            // Supprimer le topic de la base de données
            $manager->remove($topic);
            
            // Enregistrer les modifications dans la base de données
            $manager->flush();
        }

        // Rediriger vers la liste des topics dans le forum
        return $this->redirectToRoute('forum');
    }

    // -------------------------------------------------------------------------
    // Création d'un nouveau post
    // Cette méthode permet de créer un nouveau post dans un topic existant.
    // Elle récupère le topic et associe le nouveau post à l'utilisateur connecté.
    // -------------------------------------------------------------------------

    #[Route('/forum/new-post/{topicId}/{categoryId}', name: 'new_post')]
    public function newPost(

        Request $request, 
        EntityManagerInterface $manager, 
        TopicRepository $topicRepository, 
        string $topicId, 
        string $categoryId

    ): Response {

        // Récupérer le topic avec l'ID donné
        $topic = $topicRepository->find($topicId);
        
        // Si le topic n'existe pas, lever une exception
        if (!$topic) {
            throw $this->createNotFoundException('Le topic avec l\'id ' . $topicId . ' n\'existe pas.');
        }

        // Créer un nouveau post
        $post = new Post();
        
        // Créer le formulaire pour le post
        $form = $this->createForm(PostType::class, $post);
        
        // Traiter la requête et soumettre les données du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et validé
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Définir la date de création du post
            $post->setCreatedAt(new \DateTimeImmutable());
            
            // Associer le post au topic
            $post->setTopic($topic);
            
            // Associer l'utilisateur au post
            $post->setUser($this->getUser());

            // Persister le post dans la base de données
            $manager->persist($post);
            
            // Enregistrer les modifications dans la base de données
            $manager->flush();

            // Rediriger vers le topic avec le nouveau post
            return $this->redirectToRoute('topic', [
                'categoryId' => $categoryId,
                'id' => $topicId
            ]);
        }

        // Rendre la vue avec le formulaire pour créer un nouveau post
        return $this->render('pages/forum/crud/newPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Édition d'un post existant
    // Cette méthode permet d'éditer un post existant. Elle vérifie si le formulaire
    // est soumis et valide, puis met à jour les informations du post.
    // -------------------------------------------------------------------------

    #[Route('/forum/post/{id}/edit', name: 'post_edit')]
    public function editPost(
        
        Request $request, 
        Post $post, 
        EntityManagerInterface $manager

    ): Response {
        // Créer le formulaire pour l'édition du post
        $form = $this->createForm(PostType::class, $post);
        
        // Traiter la requête et soumettre les données du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et validé
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Enregistrer les modifications dans la base de données
            $manager->flush();

            // Rediriger vers le topic contenant le post mis à jour
            return $this->redirectToRoute('topic', [
                'categoryId' => $post->getTopic()->getCategoryForum()->getId(),
                'id' => $post->getTopic()->getId(),
            ]);
        }

        // Rendre la vue avec le formulaire d'édition du post
        return $this->render('pages/forum/crud/editPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Suppression d'un post
    // Cette méthode permet de supprimer un post après validation du token CSRF.
    // -------------------------------------------------------------------------

    #[Route('/forum/post/{id}/delete', name: 'post_delete', methods: ['POST'])]
    public function deletePost(
        
        Request $request, 
        Post $post, 
        EntityManagerInterface $manager
        
    ): Response {
        // Vérifier la validité du token CSRF pour la suppression
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {

            // Supprimer le post de la base de données
            $manager->remove($post);
            
            // Enregistrer les modifications dans la base de données
            $manager->flush();
        }

        // Rediriger vers le topic après la suppression du post
        return $this->redirectToRoute('topic', [
            'categoryId' => $post->getTopic()->getCategoryForum()->getId(),
            'id' => $post->getTopic()->getId(),
        ]);
    }
}
