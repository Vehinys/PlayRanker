<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{

    #[Route('/admin', name: 'admin', methods: ['GET', 'POST'])]
    public function index(

    ): Response {

        return $this->render('admin/index.html.twig', [

        'form' => $form

        ]);
    }

    #[Route('/admin/new', name: 'admin.add', methods: ['GET', 'POST'])]
    public function newSession(
        
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

        return $this->render('admin/index.html.twig', [
            'form' => $form,
            'sessionId'=> $category->getId()
        ]);
    }

    // #[Route('/admin/edition/{id}', name: 'admin.edit', methods: ['GET', 'POST'])]
    // public function editCategory(
        
    //     int $id, 
    //     CategoryRepository $repository,  
    //     Request $request, 
    //     EntityManagerInterface $manager
        
    //     ): Response {
    //     $category = $repository->find($id);

    //     if (!$category) {
    //         throw $this->createNotFoundException('category non trouvé');
    //     }

    //     $form = $this->createForm(CategoryType::class, $category);
    //     $form->handleRequest($request);

    //     // dd($category);
    //     if ($form->isSubmitted() && $form->isValid()) {

    //         $category = $form->getData();

    //         $manager->persist($category);
    //         $manager->flush();

    //         $this->addFlash(
    //             'success',
    //             'La modification à été faite avec succès de '
    //         );
    //         return $this->redirectToRoute('admin', ['id' => $category->getId()]);

    //     }
    //     return $this->render('admin/index.html.twig', [
    //         'form' => $form->createView(),
    //         'sessionId'=> $category->getId()
    //     ]);
    // }
}
