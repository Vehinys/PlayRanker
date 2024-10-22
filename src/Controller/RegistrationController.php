<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class RegistrationController extends AbstractController
{
    // ---------------------------------------------------------- //
    // Méthode pour gérer l'inscription des utilisateurs
    // ---------------------------------------------------------- //

    #[Route('/register', name: 'register')]
    public function register(

        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        Security $security,

    ): Response {
        
        // Création d'une nouvelle instance de l'entité User
        $user = new User();

        // Création du formulaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Obtention du mot de passe en clair
            $plainPassword = $form->get('plainPassword')->getData();
            
            // Encodage du mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            
            // Assurer que ROLE_USER est ajouté si aucun rôle n'est défini
            if (empty($user->getRoles())) {
                $user->setRoles(['ROLE_USER']);
            }

            // Persistance et enregistrement en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection après inscription réussie
            return $this->redirectToRoute('login');
        }

        // Rendu du formulaire d'inscription
        return $this->render('pages/security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
