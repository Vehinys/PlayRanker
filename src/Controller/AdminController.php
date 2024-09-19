<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
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
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
    
        return $this->render('admin/index.html.twig', [
            'categories' => $categories
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

    // #[Route('/admin/platform/new', name: 'platform.add', methods: ['GET', 'POST'])]
    // public function newSousCategory(
        
    //     Request $request,
    //     EntityManagerInterface $manager
        
    // ): Response {

    //     $SousCategory = new SousCategory();
    //     $form = $this->createForm(CategoryType::class, $SousCategory);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {

    //         $SousCategory = $form->getData();
    //         $manager->persist($SousCategory);
    //         $manager->flush();

    //         return $this->redirectToRoute('admin');
    //     }   

    //     return $this->render('admin/platformAdd.html.twig', [
    //         'form' => $form,
    //         'sessionId'=> $SousCategory->getId()
    //     ]);
    // }
    

    
}
