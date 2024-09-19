<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Entity\SousCategory;
use App\Form\SousCategoryType;
use App\Repository\CategoryRepository;
use App\Repository\SousCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{

    #[Route('/admin', name: 'admin')]
    public function index(
        
        CategoryRepository $categoryRepository, 
        SousCategoryRepository $sousCategoryRepository
        
    ): Response {

        $categories = $categoryRepository->findAll();
        $subcategory = $sousCategoryRepository->findAll();
    
        return $this->render('admin/index.html.twig', [
            'categories' => $categories,
            'subcategory' => $subcategory,
        ]);
    }

    #[Route('/admin/platform/new', name: 'platform.add', methods: ['GET', 'POST'])]
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

        return $this->render('admin/platformAdd.html.twig', [
            'form' => $form,
            'sessionId'=> $category->getId()
        ]);
    }

    #[Route('/admin/platform/edit/{categoryId}', name: 'platform.edit', methods: ['GET', 'POST'])]
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
            $category = $form->getData();
            $manager->persist($category);
            $manager->flush();
    
            $this->addFlash('success', 'The category has been successfully updated');
            return $this->redirectToRoute('admin', ['id' => $category->getId()]);
        }
    
        return $this->render('admin/platformEdit.html.twig', [
            'form' => $form->createView(),
            'categoryId' => $category->getId(),
        ]);
    }

    

    #[Route('/admin/platform/delete/{categoryId}', name: 'platform.delete', methods: ['POST'])]
    public function deleteCategory(
        int $categoryId, 
        CategoryRepository $repository, 
        EntityManagerInterface $manager, 
        Request $request, 
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $category = $repository->find($categoryId);
    
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
    
        // Vérification du token CSRF
        $csrfToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete'.$categoryId, $csrfToken))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
    
        // Supprimer la catégorie
        $manager->remove($category);
        $manager->flush();
    
        $this->addFlash('success', 'The category has been successfully deleted');
        
        return $this->redirectToRoute('admin');
    }


    //                 ----------------------------------------------------------------------------- // 

    #[Route('/admin/subcategory/new', name: 'subcategory.add', methods: ['GET', 'POST'])]
    public function newsubcategory(
        
        Request $request,
        EntityManagerInterface $manager
        
    ): Response {

        $subcategory = new SousCategory();
        $form = $this->createForm(SousCategoryType::class, $subcategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $subcategory = $form->getData();
            $manager->persist($subcategory);
            $manager->flush();

            return $this->redirectToRoute('admin');
        }   

        return $this->render('admin/subcategoryadd.html.twig', [
            'form' => $form,
            'sessionId'=> $subcategory->getId()
        ]);
    }

    #[Route('/admin/subcategory/edit/{subcategoryId}', name: 'subcategory.edit', methods: ['GET', 'POST'])]
    public function editsubcategory(

        int $subcategoryId, 
        SousCategoryRepository $repository,  
        Request $request, 
        EntityManagerInterface $manager

    ): Response {

        $subcategory = $repository->find($subcategoryId);
    
        if (!$subcategory) {
            throw $this->createNotFoundException('ubcategory not found');
        }
    
        $form = $this->createForm(SousCategoryType::class, $subcategory);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $subcategory = $form->getData();
            $manager->persist($subcategory);
            $manager->flush();
    
            $this->addFlash('success', 'The category has been successfully updated');
            return $this->redirectToRoute('admin', ['id' => $subcategory->getId()]);
        }
    
        return $this->render('admin/platformEdit.html.twig', [
            'form' => $form->createView(),
            'subcategoryId' => $subcategory->getId(),
        ]);
    }

    

    #[Route('/admin/subcategory/delete/{subcategoryId}', name: 'subcategory.delete', methods: ['POST'])]
    public function deletesubcategory(

        int $subcategoryId, 
        SousCategoryRepository $repository, 
        EntityManagerInterface $manager, 
        Request $request, 
        CsrfTokenManagerInterface $csrfTokenManager

    ): Response {
        $subcategory = $repository->find($subcategoryId);
    
        if (!$subcategory) {
            throw $this->createNotFoundException('Category not found');
        }
    
        // Vérification du token CSRF
        $csrfToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete'.$subcategoryId, $csrfToken))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
    
        // Supprimer la catégorie
        $manager->remove($subcategory);
        $manager->flush();
    
        $this->addFlash('success', 'The subcategory has been successfully deleted');
        
        return $this->redirectToRoute('admin');
    }

    

    
}
