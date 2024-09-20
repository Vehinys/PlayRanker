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
    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Affiche la page du tableau de bord administrateur, présentant une liste de toutes les catégories et sous-catégories.
     *
     * @param CategoryRepository $categoryRepository Le repository pour gérer les entités Category.
     * @param SousCategoryRepository $sousCategoryRepository Le repository pour gérer les entités SousCategory.
     *
     * @return Response La page du tableau de bord administrateur rendue.
     */

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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Gère la création d'une nouvelle catégorie.
     *
     * Cette action est mappée à la route '/admin/platform/new' avec le nom 'platform.add'.
     * Elle permet à l'utilisateur de créer une nouvelle catégorie en affichant un formulaire et en traitant sa soumission.
     *
     * @param Request $request La requête HTTP courante.
     * @param EntityManagerInterface $manager Le gestionnaire d'entités pour persister la nouvelle catégorie.
     *
     * @return Response Le template rendu pour le formulaire de création de catégorie.
     */

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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Gère la modification d'une catégorie existante.
     *
     * Cette action est mappée à la route '/admin/platform/edit/{categoryId}' avec le nom 'platform.edit'.
     * Elle permet à l'utilisateur de modifier une catégorie existante en affichant un formulaire et en traitant sa soumission.
     *
     * @param int $categoryId L'ID de la catégorie à modifier.
     * @param CategoryRepository $repository Le repository pour récupérer la catégorie.
     * @param Request $request La requête HTTP courante.
     * @param EntityManagerInterface $manager Le gestionnaire d'entités pour persister la catégorie mise à jour.
     *
     * @return Response Le template rendu pour le formulaire de modification de la catégorie.
     */

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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Gère la suppression d'une catégorie existante.
     *
     * Cette action est mappée à la route '/admin/platform/delete/{categoryId}' avec le nom 'platform.delete'.
     * Elle permet à l'utilisateur de supprimer une catégorie existante en traitant la soumission du formulaire et en supprimant la catégorie de la base de données.
     *
     * @param int $categoryId L'ID de la catégorie à supprimer.
     * @param CategoryRepository $repository Le repository pour récupérer la catégorie.
     * @param EntityManagerInterface $manager Le gestionnaire d'entités pour supprimer la catégorie.
     * @param Request $request La requête HTTP courante.
     * @param CsrfTokenManagerInterface $csrfTokenManager Le gestionnaire de jetons CSRF pour vérifier le jeton CSRF.
     *
     * @return Response La réponse de redirection vers la page d'administration.
     */

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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Gère la création d'une nouvelle sous-catégorie.
     *
     * Cette action est mappée à la route '/admin/subcategory/new' avec le nom 'subcategory.add'.
     * Elle permet à l'utilisateur de créer une nouvelle sous-catégorie en traitant la soumission du formulaire et en persistant la sous-catégorie dans la base de données.
     *
     * @param Request $request La requête HTTP courante.
     * @param EntityManagerInterface $manager Le gestionnaire d'entités pour persister la sous-catégorie.
     *
     * @return Response La réponse rendue pour le template d'ajout de sous-catégorie.
     */


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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */
    
    /**
     * Gère la modification d'une sous-catégorie existante.
     *
     * Cette action est mappée à la route '/admin/subcategory/edit/{subcategoryId}' avec le nom 'subcategory.edit'.
     * Elle permet à l'utilisateur de modifier une sous-catégorie existante en traitant la soumission du formulaire et en persistant la sous-catégorie mise à jour dans la base de données.
     *
     * @param int $subcategoryId L'ID de la sous-catégorie à modifier.
     * @param SousCategoryRepository $repository Le repository pour récupérer la sous-catégorie.
     * @param Request $request La requête HTTP courante.
     * @param EntityManagerInterface $manager Le gestionnaire d'entités pour persister la sous-catégorie mise à jour.
     *
     * @return Response La réponse rendue pour le template de modification de la sous-catégorie.
     */


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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Gère la suppression d'une sous-catégorie existante.
     *
     * Cette action est mappée à la route '/admin/subcategory/delete/{subcategoryId}' avec le nom 'subcategory.delete'.
     * Elle permet à l'utilisateur de supprimer une sous-catégorie existante en traitant la soumission du formulaire et en supprimant la sous-catégorie de la base de données.
     *
     * @param int $subcategoryId L'ID de la sous-catégorie à supprimer.
     * @param SousCategoryRepository $repository Le repository pour récupérer la sous-catégorie.
     * @param EntityManagerInterface $manager Le gestionnaire d'entités pour supprimer la sous-catégorie.
     * @param Request $request La requête HTTP courante.
     * @param CsrfTokenManagerInterface $csrfTokenManager Le gestionnaire de jetons CSRF pour vérifier le jeton CSRF.
     *
     * @return Response La réponse de redirection vers la route admin.
     */


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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

}
