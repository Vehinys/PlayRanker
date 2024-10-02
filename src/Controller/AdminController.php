<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Entity\Platform;
use App\Form\PlatformType;
use App\Repository\CategoryRepository;
use App\Repository\PlatformRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
/* ----------------------------------------------------------------------------------------------------------------------------------------- */

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/admin', name: 'admin')]
    public function index(
        
        CategoryRepository $categoryRepository, 
        PlatformRepository $PlatformRepository
        
    ): Response {

        $categories = $categoryRepository->findAll();
        $platforms = $PlatformRepository->findAll();
    
        return $this->render('admin/index.html.twig', [
            'categories' => $categories,
            'platforms' => $platforms,
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */


    #[Route('/admin/category/new', name: 'category.add', methods: ['GET', 'POST'])]
    public function newCategory(
        
        Request $request,
        EntityManagerInterface $manager
        
    ): Response {

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $category = $form->getData();
            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('admin');
        }   

        return $this->render('admin/categoryAdd.html.twig', [
            'form' => $form,
            'sessionId'=> $category->getId()
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/admin/category/edit/{categoryId}', name: 'category.edit', methods: ['GET', 'POST'])]
    public function editCategory(
        int $categoryId, 
        CategoryRepository $repository,  
        Request $request, 
        EntityManagerInterface $manager
    ): Response {
        $category = $repository->find($categoryId);
    
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
    
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($category);
            $manager->flush();
    
            $this->addFlash('success', 'The category has been successfully updated');
            return $this->redirectToRoute('admin');
        }
    
        return $this->render('admin/categoryEdit.html.twig', [
            'form' => $form->createView(),
            'categoryId' => $category->getId(),
        ]);
    }
    
/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/admin/category/delete/{categoryId}', name: 'category.delete', methods: ['POST'])]
    public function deleteCategory(

        int $categoryId,
        CategoryRepository $repository,
        EntityManagerInterface $manager,

    ): Response {
        $category = $repository->find($categoryId);

        // Supprimer la catégorie
        $manager->remove($category);
        $manager->flush();

        $this->addFlash('success', 'The category has been successfully deleted');

        return $this->redirectToRoute('admin');
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/admin/platform/new', name: 'platform.add', methods: ['GET', 'POST'])]
    public function addPlatform(
        
        Request $request,
        EntityManagerInterface $manager
        
    ): Response {

        $Platform = new Platform();
        $form = $this->createForm(PlatformType::class, $Platform);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $Platform = $form->getData();
            $manager->persist($Platform);
            $manager->flush();

            return $this->redirectToRoute('admin');
        }   

        return $this->render('admin/platformadd.html.twig', [
            'form' => $form,
            'sessionId'=> $Platform->getId()
        ]);
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */
    
#[Route('/admin/platform/edit/{platformId}', name: 'platform.edit', methods: ['GET', 'POST'])]
public function editPlatform(
    int $platformId, 
    PlatformRepository $repository,  
    Request $request, 
    EntityManagerInterface $manager
): Response {

    // Recherche de la plateforme par son ID
    $platform = $repository->find($platformId);

    // Si la plateforme n'est pas trouvée, on lance une exception
    if (!$platform) {
        throw $this->createNotFoundException('Platform not found');
    }

    // Création du formulaire en utilisant PlatformType
    $form = $this->createForm(PlatformType::class, $platform);
    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        $platform = $form->getData();
        $manager->persist($platform);
        $manager->flush();

        // Message flash pour indiquer que la plateforme a été mise à jour
        $this->addFlash('success', 'The platform has been successfully updated');
        
        // Redirection vers l'édition de la plateforme avec l'ID mis à jour
        return $this->redirectToRoute('platform.edit', ['platformId' => $platform->getId()]);
    }

    return $this->render('admin/platformEdit.html.twig', [
        'form' => $form->createView(),
        'platformId' => $platform->getId(),
    ]);
}
    
/* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/admin/platform/delete/{platformId}', name: 'platform.delete', methods: ['POST'])]
    public function deletePlatform(

        int $platformId, 
        PlatformRepository $repository, 
        EntityManagerInterface $manager, 

    ): Response {
        $platform = $repository->find($platformId);
    
        // Supprimer la catégorie
        $manager->remove($platform);
        $manager->flush();
    
        $this->addFlash('success', 'The platform has been successfully deleted');
        
        return $this->redirectToRoute('admin');
    }

/* ----------------------------------------------------------------------------------------------------------------------------------------- */
    
}
