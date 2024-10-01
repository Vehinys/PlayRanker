<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\GamesList;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(

        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        GamesList $gameList = null,

    ): Response {
        // Création d'une nouvelle instance de l'entité User
        $user = new User();
        // Création des nouvelles instance de l'entité GamesList
        $gameList = new GamesList();
        $gameList1 = new GamesList();
        $gameList2 = new GamesList();
        $gameList3 = new GamesList();

        
        // Dés qu'un utilisateur s'inscrit, il a 4 listes de jeux par défaut
        $gameList->setName('Favoris');
        $gameList1->setName('Already played');
        $gameList2->setName('My desires');
        $gameList3->setName('Go test');
        
        // Création du formulaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Obtention du mot de passe en clair
            $plainPassword = $form->get('plainPassword')->getData();
            
            // Encodage du mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            
            // Associer la liste nouvellement créer au nouvel utilisateur
            $user->addGamesList($gameList);
            $user->addGamesList($gameList1);
            $user->addGamesList($gameList2);
            $user->addGamesList($gameList3);
            
            // Assurer que ROLE_USER est ajouté si aucun rôle n'est défini
            if (empty($user->getRoles())) {
                $user->setRoles(['ROLE_USER']);
            }

            // Persistance et enregistrement en base de données
            $entityManager->persist($user);
            $entityManager->persist($gameList);
            $entityManager->persist($gameList1);
            $entityManager->persist($gameList2);
            $entityManager->persist($gameList3);
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
