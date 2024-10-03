<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\TypeType;
use App\Repository\CategoryRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contrôleur pour les types
 */
class TypeController extends AbstractController
{

    // /**
    //  * Affiche la liste des types
    //  **/

    // #[Route('/admin/types', name: 'type_index')]
    // public function index(

    //     TypeRepository $categorytypeRepository,
    //     CategoryRepository $categoryRepository

    // ): Response
    // {
    //     // Récupère tous les types
    //     $types = $categorytypeRepository->findBy([], ['name' => 'ASC']);

    //     // Affiche la liste des types
    //     return $this->render('admin/index.html.twig', [

    //         'types' => $types,
            

    //     ]);
    // }

    /**
    * Crée un nouveau type
    **/

    #[Route('/admin/types/new', name: 'type_new')]
    public function new(

        EntityManagerInterface $entityManager,
        Request $request

    ): Response {
        // Crée un nouveau type
        $type = new Type();

        // Crée un formulaire pour le nouveau type
        $formType = $this->createForm(TypeType::class, $type);

        // Traite la requête HTTP
        $formType->handleRequest($request);

        // Vérifie si le formulaire est soumis et valide
        if ($formType->isSubmitted() && $formType->isValid()) {

            // Persiste le nouveau type en base de données
            $entityManager->persist($type);
            $entityManager->flush();

            // Redirige vers la liste des types
            return $this->redirectToRoute('admin');
        }

        // Affiche le formulaire pour le nouveau type
        return $this->render('admin/typeAdd.html.twig', [

            'formType' => $formType->createView()]);
    }

    /**
    * Met à jour un type existant
    **/

    #[Route('/admin/types/{id}/edit', name: 'type_edit')]
    public function edit(

        Request $request,
        Type $type,
        EntityManagerInterface $entityManager

    ): Response{

        // Crée un formulaire pour le type existant
        $formType = $this->createForm(TypeType::class, $type);

        // Traite la requête HTTP
        $formType->handleRequest($request);

        // Vérifie si le formulaire est soumis et valide
        if ($formType->isSubmitted() && $formType->isValid()) {
            // Met à jour le type existant en base de données
            $entityManager->flush();

            // Redirige vers la liste des types
            return $this->redirectToRoute('admin');
        }

        // Affiche le formulaire pour le type existant
        return $this->render('admin/typeEdit.html.twig', [
            
            'formType' => $formType->createView(), 
            'type' => $type

        ]);
    }

    /**
    * Supprime un type existant
    **/

    #[Route('/types/{id}', name: 'type_delete')]
    public function delete(

        Request $request,
        Type $type,
        EntityManagerInterface $entityManager

    ): Response {

        // Vérifie si le token CSRF est valide
        if (!$this->isCsrfTokenValid('delete', $request->request->get('_token'))) {
            
            // Redirige vers la liste des types 
            return $this->redirectToRoute('admin');
        }

        // Supprime le type existant de la base de données
        $entityManager->remove($type);
        $entityManager->flush();

        // Redirige vers la liste des types
        return $this->redirectToRoute('admin');
    }
}