<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Platform;
use App\Form\CategoryType;
use App\Form\PlatformType;
use App\Repository\TypeRepository;
use App\Repository\CategoryRepository;
use App\Repository\PlatformRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    // ---------------------------------------------------------- //
    // Affiche la page d'administration
    // ---------------------------------------------------------- //

    /**
     * Displays the admin page with a list of all categories, platforms, and types.
     *
     * @param CategoryRepository $categoryRepository The repository for fetching categories.
     * @param PlatformRepository $PlatformRepository The repository for fetching platforms.
     * @param TypeRepository $typeRepository The repository for fetching types.
     *
     * @return Response The rendered admin page with the fetched data.
     */

        #[Route('/admin', name: 'admin')]
        #[IsGranted('ROLE_ADMIN')]
        public function index(

            CategoryRepository $categoryRepository, 
            PlatformRepository $PlatformRepository,
            TypeRepository $typeRepository

        ): Response {

            // Récupération de toutes les catégories, plateformes et types
            $categories = $categoryRepository->findAll();
            $platforms = $PlatformRepository->findAll();
            $types = $typeRepository->findAll();
        
            // Rendu de la vue avec les données récupérées
            return $this->render('admin/index.html.twig', [
                'categories' => $categories,
                'platforms' => $platforms,
                'types' => $types,
            ]);
        }

    // ---------------------------------------------------------- //
    // Ajoute une nouvelle catégorie
    // ---------------------------------------------------------- //

    /**
     * Adds a new category to the database.
     *
     * @param Request $request The current HTTP request.
     * @param EntityManagerInterface $manager The entity manager for persisting the new category.
     *
     * @return Response The response redirecting to the admin page after the new category is created.
     */

        #[Route('/admin/category/new', name: 'category.add', methods: ['GET', 'POST'])]
        public function newCategory(

            Request $request,
            EntityManagerInterface $manager

        ): Response {

            // Création d'une nouvelle instance de Category
            $category = new Category();
            
            // Création du formulaire pour la catégorie
            $form = $this->createForm(CategoryType::class, $category);
            $form->handleRequest($request);

            // Traitement du formulaire soumis
            if ($form->isSubmitted() && $form->isValid()) {
                $category = $form->getData();
                $manager->persist($category);
                $manager->flush();

                // Redirection vers la page d'administration
                return $this->redirectToRoute('admin');
            }   

            // Rendu de la vue avec le formulaire
            return $this->render('admin/categoryAdd.html.twig', [
                'form' => $form,
                'sessionId'=> $category->getId()
            ]);
        }
    
    // ---------------------------------------------------------- //
    // Modifie une catégorie existante
    // ---------------------------------------------------------- //

    /**
     * Edits an existing category in the database.
     *
     * @param int $categoryId The ID of the category to edit.
     * @param CategoryRepository $repository The repository for fetching the category.
     * @param Request $request The current HTTP request.
     * @param EntityManagerInterface $manager The entity manager for persisting changes.
     *
     * @return Response The response redirecting to the admin page after the category is updated.
     */

        #[Route('/admin/category/edit/{categoryId}', name: 'category.edit', methods: ['GET', 'POST'])]
        public function editCategory(

            int $categoryId, 
            CategoryRepository $repository,  
            Request $request, 
            EntityManagerInterface $manager

        ): Response {

            // Récupération de la catégorie à modifier
            $category = $repository->find($categoryId);
        
            // Vérification de l'existence de la catégorie
            if (!$category) {
                throw $this->createNotFoundException('Catégorie non trouvée');
            }
        
            // Création du formulaire pour la catégorie
            $form = $this->createForm(CategoryType::class, $category);
            $form->handleRequest($request);
        
            // Traitement du formulaire soumis
            if ($form->isSubmitted() && $form->isValid()) {
                $manager->persist($category);
                $manager->flush();
        
                $this->addFlash('success', 'La catégorie a été mise à jour avec succès');
                return $this->redirectToRoute('admin');
            }
        
            // Rendu de la vue avec le formulaire
            return $this->render('admin/categoryEdit.html.twig', [
                'form' => $form->createView(),
                'categoryId' => $category->getId(),
            ]);
        }
    
    // ---------------------------------------------------------- //
    // Supprime une catégorie
    // ---------------------------------------------------------- //

    /**
     * Deletes a category from the database.
     *
     * @param int $categoryId The ID of the category to delete.
     * @param CategoryRepository $repository The repository for fetching the category.
     * @param EntityManagerInterface $manager The entity manager for persisting changes.
     *
     * @return Response The response redirecting to the admin page after the category is deleted.
     */

        #[Route('/admin/category/delete/{categoryId}', name: 'category.delete', methods: ['POST'])]
        public function deleteCategory(

            int $categoryId,
            CategoryRepository $repository,
            EntityManagerInterface $manager

        ): Response {

            // Récupération de la catégorie à supprimer
            $category = $repository->find($categoryId);

            // Suppression de la catégorie
            $manager->remove($category);
            $manager->flush();

            // Message flash de confirmation
            $this->addFlash('success', 'La catégorie a été supprimée avec succès');

            // Redirection vers la page d'administration
            return $this->redirectToRoute('admin');
        }

    // ---------------------------------------------------------- //
    // Ajoute une nouvelle plateforme
    // ---------------------------------------------------------- //

    /**
     * Adds a new platform to the database.
     *
     * @param Request $request The current HTTP request.
     * @param EntityManagerInterface $manager The entity manager for persisting changes.
     *
     * @return Response The response redirecting to the admin page after the platform is added.
     */

        #[Route('/admin/platform/new', name: 'platform.add', methods: ['GET', 'POST'])]
        public function addPlatform(

            Request $request,
            EntityManagerInterface $manager

        ): Response {

            // Création d'une nouvelle instance de Platform
            $Platform = new Platform();
            
            // Création du formulaire pour la plateforme
            $form = $this->createForm(PlatformType::class, $Platform);
            $form->handleRequest($request);

            // Traitement du formulaire soumis
            if ($form->isSubmitted() && $form->isValid()) {
                $Platform = $form->getData();
                $manager->persist($Platform);
                $manager->flush();

                // Redirection vers la page d'administration
                return $this->redirectToRoute('admin');
            }   

            // Rendu de la vue avec le formulaire
            return $this->render('admin/platformadd.html.twig', [
                'form' => $form,
                'sessionId'=> $Platform->getId()
            ]);
        }

    // ---------------------------------------------------------- //
    // Modifie une plateforme existante
    // ---------------------------------------------------------- //
    
    /**
     * Edits an existing platform in the database.
     *
     * @param int $platformId The ID of the platform to edit.
     * @param PlatformRepository $repository The repository for managing platforms.
     * @param Request $request The current HTTP request.
     * @param EntityManagerInterface $manager The entity manager for persisting changes.
     *
     * @return Response The response redirecting to the admin page after the platform is updated.
     */

        #[Route('/admin/platform/edit/{platformId}', name: 'platform.edit', methods: ['GET', 'POST'])]
        public function editPlatform(

            int $platformId, 
            PlatformRepository $repository,  
            Request $request, 
            EntityManagerInterface $manager

        ): Response {

            // Recherche de la plateforme par son ID
            $platform = $repository->find($platformId);

            // Vérification de l'existence de la plateforme
            if (!$platform) {
                throw $this->createNotFoundException('Plateforme non trouvée');
            }

            // Création du formulaire pour la plateforme
            $form = $this->createForm(PlatformType::class, $platform);
            $form->handleRequest($request);

            // Traitement du formulaire soumis
            if ($form->isSubmitted() && $form->isValid()) {
                $platform = $form->getData();
                $manager->persist($platform);
                $manager->flush();

                // Message flash de confirmation
                $this->addFlash('success', 'La plateforme a été mise à jour avec succès');
                
                // Redirection vers la page d'administration
                return $this->redirectToRoute('admin');
            }

            // Rendu de la vue avec le formulaire
            return $this->render('admin/platformEdit.html.twig', [
                'form' => $form->createView(),
                'platformId' => $platform->getId(),
            ]);
        }

    // ---------------------------------------------------------- //
    // Supprime une plateforme
    // ---------------------------------------------------------- //

    /**
     * Deletes a platform from the database.
     *
     * @param int $platformId The ID of the platform to delete.
     * @param PlatformRepository $repository The repository for managing platforms.
     * @param EntityManagerInterface $manager The entity manager for persisting changes.
     *
     * @return Response The response redirecting to the admin page.
     */

        #[Route('/admin/platform/delete/{platformId}', name: 'platform.delete', methods: ['POST'])]
        public function deletePlatform(

            int $platformId,
            PlatformRepository $repository,
            EntityManagerInterface $manager

        ): Response {

            // Récupération de la plateforme à supprimer
            $platform = $repository->find($platformId);

            // Suppression de la plateforme
            $manager->remove($platform);
            $manager->flush();

            // Message flash de confirmation
            $this->addFlash('success', 'La plateforme a été supprimée avec succès');

            // Redirection vers la page d'administration
            return $this->redirectToRoute('admin');
        }
}
