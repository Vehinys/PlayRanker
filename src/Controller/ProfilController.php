<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Form\ProfilType;
use App\Entity\GamesList;
use App\Repository\GameRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Repository\GamesListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfilController extends AbstractController
{
    // Déclaration de la propriété pour le hachage de mot de passe
    private UserPasswordHasherInterface $passwordHasher;

    // Constructeur de la classe
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    // ---------------------------------------------------------- //
    // Affiche le profil de l'utilisateur
    // ---------------------------------------------------------- //
    
    /**
     * Displays the user's profile page.
     *
     * This action retrieves the currently authenticated user, all game list types, and the game lists associated with the user.
     * It then renders the 'pages/profil/index.html.twig' template with the retrieved data.
     *
     * @param GamesListRepository $gamesListRepository The repository for managing game lists.
     * @param TypeRepository $typeRepository The repository for managing game list types.
     *
     * @return Response The rendered profile page.
     */

    #[Route('/profil/{username}', name: 'profil', methods: ["GET", "POST"])]
    public function index(

        string $username,
        TypeRepository $typeRepository,
        UserRepository $userRepository,

    ): Response {
    
        // Récupération de l'utilisateur connecté
        $currentUser = $this->getUser();
    
        // Récupération de l'utilisateur cible à partir du username
        $targetUser = $userRepository->findOneBy(['username' => $username]);
    
        // Vérifiez si l'utilisateur cible existe
        if (!$targetUser) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
    
        // Récupération de tous les types de listes
        $types = $typeRepository->findAll();

        // Récupération des listes de jeux associées à l'utilisateur cible
        $gamesLists = $targetUser->getGamesLists(); 
    
        // Rendu de la vue avec les données récupérées
        return $this->render('pages/profil/index.html.twig', [
            'gamesLists' => $gamesLists,
            'user' => $targetUser,
            'types' => $types,
            'currentUser' => $currentUser,
        ]);
    }
    
    // ---------------------------------------------------------- //
    // Modifie le profil de l'utilisateur
    // ---------------------------------------------------------- //

    #[Route('/profil/edit/{username}', name: 'edit_profil' )]
    public function editProfile(

        string $username,
        Request $request, 
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        SluggerInterface $slugger
        
    ): Response {
        // Récupération du répertoire pour les avatars
        $avatarDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/avatars';

        // Récupérer l'utilisateur par username
        $user = $userRepository->findOneBy(['username' => $username]);
        
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        
        $form = $this->createForm(ProfilType::class, $user);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                try {

                    $avatarFile->move($avatarDirectory, $newFilename);
                    $user->setAvatar($newFilename);

                } catch (FileException $e) {

                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'avatar.');
                    return $this->redirectToRoute('edit_profil', ['username' => $username]);

                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('profil', ['username' => $this->getUser()->getUsername()]);
        }
        
        return $this->render('pages/profil/editProfil.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }


    // ---------------------------------------------------------- //
    // Supprime le profil de l'utilisateur
    // ---------------------------------------------------------- //

    #[Route('/profil/delete', name: 'delete_profil')]
    public function deleteProfile(

        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session

    ): Response {

        // Récupération de l'utilisateur courant
        $user = $this->getUser();

        // Vérification de la validité de l'utilisateur
        if (!$user instanceof User) {
            throw new \Exception('L\'utilisateur est invalide');
        }

        // Anonymisation de l'utilisateur
        $user->anonymize($this->passwordHasher);

        // Sauvegarde des modifications
        $entityManager->flush();

        // Déconnexion de l'utilisateur
        $tokenStorage->setToken(null);
        $session->invalidate();

        // Redirection vers la page d'inscription
        return $this->redirectToRoute('register');
    }


    // ---------------------------------------------------------- //
    // Ajoute un jeu aux favoris
    // ---------------------------------------------------------- //

    #[Route('/jeux/{id}/addFavorite', name: 'addFavorite')]
    public function addFavorite(

        int $id,
        Request $request,
        EntityManagerInterface $manager,
        GamesListRepository $gameListRepository,
        GameRepository $gameRepository,
        TypeRepository $typeRepository

    ): Response {
        // Récupération de l'utilisateur et du type "Favoris"
        $user = $this->getUser();
        $favoriteType = $typeRepository->findOneBy(['name' => 'Favoris']);

        // Vérification de l'existence du jeu
        $existingGame = $gameRepository->findOneBy(['id_game_api' => $id]);

        if (!$existingGame) {
            // Création d'un nouveau jeu s'il n'existe pas
            $game = new Game();
            $game->setIdGameApi($id);
            $game->setName($request->query->get('gameName'));
            $game->setData([$request->query->get('gameData')]);
            $manager->persist($game);
        } else {
            $game = $existingGame;
        }

        // Vérification si le jeu est déjà en favori
        $existingFavorite = $gameListRepository->findOneBy([
            'user' => $user,
            'game' => $game,
            'type' => $favoriteType
        ]);

        if ($existingFavorite) {
            // Suppression du favori s'il existe déjà
            $manager->remove($existingFavorite);
        } else {
            // Ajout du jeu aux favoris
            $newFavorite = new GamesList();
            $newFavorite->setUser($user);
            $newFavorite->setGame($game);
            $newFavorite->setType($favoriteType);
            $manager->persist($newFavorite);
        }

        // Sauvegarde des modifications
        $manager->flush();

        // Redirection vers la page des jeux
        return $this->redirectToRoute('jeux', ['page' => 1]); 
    }

    // ---------------------------------------------------------- //
    // Ajoute un jeu à la liste "Déjà joué"
    // ---------------------------------------------------------- //

    #[Route('/jeux/{id}/addAlreadyPlayed', name: 'addAlreadyPlayed')]
    public function addAlreadyPlayed(

        int $id,
        Request $request,
        EntityManagerInterface $manager,
        GamesListRepository $gameListRepository,
        GameRepository $gameRepository,
        TypeRepository $typeRepository

    ): Response {
        // Récupération de l'utilisateur et du type "Already played"
        $user = $this->getUser();
        $alreadyPlayedType = $typeRepository->findOneBy(['name' => 'Already played']);

        // Vérification de l'existence du jeu
        $existingGame = $gameRepository->findOneBy(['id_game_api' => $id]);

        if (!$existingGame) {
            // Création d'un nouveau jeu s'il n'existe pas
            $game = new Game();
            $game->setIdGameApi($id);
            $game->setName($request->query->get('gameName'));
            $game->setData([$request->query->get('gameData')]);
            $manager->persist($game);
        } else {
            $game = $existingGame;
        }

        // Vérification si le jeu est déjà dans la liste "Déjà joué"
        $existingAlreadyPlayed = $gameListRepository->findOneBy([
            'user' => $user,
            'game' => $game,
            'type' => $alreadyPlayedType
        ]);

        if ($existingAlreadyPlayed) {
            // Suppression de l'entrée si elle existe déjà
            $manager->remove($existingAlreadyPlayed);
        } else {
            // Ajout du jeu à la liste "Déjà joué"
            $newAlreadyPlayed = new GamesList();
            $newAlreadyPlayed->setUser($user);
            $newAlreadyPlayed->setGame($game);
            $newAlreadyPlayed->setType($alreadyPlayedType);
            $manager->persist($newAlreadyPlayed);
        }

        // Sauvegarde des modifications
        $manager->flush();

        // Redirection vers la page des jeux
        return $this->redirectToRoute('jeux', ['page' => 1]);
    }

    // ---------------------------------------------------------- //
    // Ajoute un jeu à la liste "Mes envies"
    // ---------------------------------------------------------- //

    #[Route('/jeux/{id}/addMyDesires', name: 'addMyDesires')]
    public function addMyDesires(

        int $id,
        Request $request,
        EntityManagerInterface $manager,
        GamesListRepository $gameListRepository,
        GameRepository $gameRepository,
        TypeRepository $typeRepository

    ): Response {

        // Récupération de l'utilisateur et du type "My desires"
        $user = $this->getUser();
        $myDesiresType = $typeRepository->findOneBy(['name' => 'My desires']);

        // Vérification de l'existence du jeu
        $existingGame = $gameRepository->findOneBy(['id_game_api' => $id]);

        if (!$existingGame) {
            // Création d'un nouveau jeu s'il n'existe pas
            $game = new Game();
            $game->setIdGameApi($id);
            $game->setName($request->query->get('gameName'));
            $game->setData([$request->query->get('gameData')]);
            $manager->persist($game);
        } else {
            $game = $existingGame;
        }

        // Vérification si le jeu est déjà dans la liste "Mes envies"
        $existingMyDesire = $gameListRepository->findOneBy([
            'user' => $user,
            'game' => $game,
            'type' => $myDesiresType
        ]);

        if ($existingMyDesire) {
            // Suppression de l'entrée si elle existe déjà
            $manager->remove($existingMyDesire);
        } else {
            // Ajout du jeu à la liste "Mes envies"
            $newMyDesire = new GamesList();
            $newMyDesire->setUser($user);
            $newMyDesire->setGame($game);
            $newMyDesire->setType($myDesiresType);
            $manager->persist($newMyDesire);
        }

        // Sauvegarde des modifications
        $manager->flush();

        // Redirection vers la page des jeux
        return $this->redirectToRoute('jeux', ['page' => 1]);
    }

    // ---------------------------------------------------------- //
    // Ajoute un jeu à la liste "À tester"
    // ---------------------------------------------------------- //

    #[Route('/jeux/{id}/addGoTest', name: 'addGoTest')]
    public function addGoTest(

        int $id,
        Request $request,
        EntityManagerInterface $manager,
        GamesListRepository $gameListRepository,
        GameRepository $gameRepository,
        TypeRepository $typeRepository

    ): Response {
        // Récupération de l'utilisateur et du type "Go test"
        $user = $this->getUser();
        $goTestType = $typeRepository->findOneBy(['name' => 'Go test']);

        // Vérification de l'existence du jeu
        $existingGame = $gameRepository->findOneBy(['id_game_api' => $id]);

        if (!$existingGame) {
            // Création d'un nouveau jeu s'il n'existe pas
            $game = new Game();
            $game->setIdGameApi($id);
            $game->setName($request->query->get('gameName'));
            $game->setData([$request->query->get('gameData')]);
            $manager->persist($game);
        } else {
            $game = $existingGame;
        }

        // Vérification si le jeu est déjà dans la liste "À tester"
        $existingGoTest = $gameListRepository->findOneBy([
            'user' => $user,
            'game' => $game,
            'type' => $goTestType
        ]);

        if ($existingGoTest) {
            // Suppression de l'entrée si elle existe déjà
            $manager->remove($existingGoTest);
        } else {
            // Ajout du jeu à la liste "À tester"
            $newGoTest = new GamesList();
            $newGoTest->setUser($user);
            $newGoTest->setGame($game);
            $newGoTest->setType($goTestType);
            $manager->persist($newGoTest);
        }

        // Sauvegarde des modifications
        $manager->flush();

        // Redirection vers la page des jeux
        return $this->redirectToRoute('jeux', ['page' => 1]);
    }
}

