<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route; // Utiliser Annotation au lieu de Attribute si pas en PHP 8.0

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(
        
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager
        
    ): Response {
        
        // Création d'une nouvelle instance de User
        $user = new User();
        
        // Création du formulaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
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

