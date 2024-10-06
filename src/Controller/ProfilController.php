<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GamesList;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\GamesListRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    // ---------------------------------------------------------- //
    // Affiche le profil de l'utilisateur
    // ---------------------------------------------------------- //
    
    #[Route('/profil', name: 'profil')]
    public function index(

        GamesListRepository $repository

    ): Response {

        // Récupérer l'utilisateur courant
        $user = $this->getUser();

        // Récupère la liste des favoris pour cet utilisateur
        $favoris = $repository->findBy(['user' => $user, 'type' => 1]);
        $alreadyPlayed = $repository->findOneBy(['user' => $user, 'id' => 2]);
        $myDesires = $repository->findOneBy(['user' => $user, 'id' => 3]);
        $goTest = $repository->findOneBy(['user' => $user, 'id' => 4]);

        $game = $repository->findAll();

        return $this->render('pages/profil/index.html.twig', [
            'user' => $user,
            'favoris' => $favoris,
            'alreadyPlayed' => $alreadyPlayed,
            'myDesires' => $myDesires,
            'goTest' => $goTest,
        ]);
    }

    // ---------------------------------------------------------- //
    // Modifie le profil de l'utilisateur
    // ---------------------------------------------------------- //
    
    #[Route('/profil/editProfil', name: 'edit_profile')]
    public function editProfile(
        
        Request $request, 
        EntityManagerInterface $entityManager
        
        ): Response {

        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
            ->add('pseudo', TextType::class, [
                'label' => 'Nouveau Pseudo',
                'attr' => [
                    'placeholder' => 'Entrez votre nouveau pseudo',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un pseudo.']),
                    new Length(['min' => 2, 'max' => 100]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email.'])
                ],
            ])
            ->add('avatar', UrlType::class, [
                'label' => 'Avatar',
                'attr' => [
                    'placeholder' => 'Url de l\'avatar',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('profil');
        }

        return $this->render('pages/profil/editProfil.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    // ---------------------------------------------------------- //
    // Supprime le profil de l'utilisateur
    // ---------------------------------------------------------- //
    
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        
        UserPasswordHasherInterface $passwordHasher
        
        ) {

        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/profil/delete', name: 'delete_profile')]
    public function deleteProfile(

        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session

    ): Response {

        // Récupérer l'utilisateur courant
        $user = $this->getUser();

        // Vérifier que l'utilisateur est bien authentifié et est une instance de User
        if (!$user instanceof User) {
            throw new \Exception('The user is invalid');
        }

        // Anonymiser l'utilisateur
        $user->anonymize($this->passwordHasher);

        // Enregistrer les changements (anonymisation)
        $entityManager->flush();

        // Déconnecter l'utilisateur
        $tokenStorage->setToken(null);
        $session->invalidate();

        // Rediriger vers la page d'inscription
        return $this->redirectToRoute('register');
    }

    // ---------------------------------------------------------- //
    // Affiche les jeux par liste
    // ---------------------------------------------------------- //
    
    #[Route('/game/list/{id}', name: 'displayGamesByList')]
    public function displayGamesByList(

        int $id,
        GamesListRepository $repository,
        Request $request,
        EntityManagerInterface $manager,
        GameRepository $gameRepository

    ): Response {

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer la liste des jeux de l'utilisateur
        $favoris = $repository->findOneBy(['user' => $user, 'name' => 'Favoris']);

        // Vérifier si le jeu existe déjà dans la base de données
        $existingGame = $gameRepository->findOneBy(['id_game_api' => $id]);

        // Si le jeu n'existe pas, créer une nouvelle instance de Game
        if (!$existingGame) {
            $game = new Game();
            $game->setIdGameApi($id);

            // Récupérer le nom et les données du jeu depuis la requête
            $gameName = $request->get('gameName');
            $gameData = $request->get('gameData');

            // Configurer les propriétés du jeu
            $game->setName($gameName);
            $game->setData([$gameData]);

            // Persister le nouveau jeu
            $manager->persist($game);
        } else {
            // Si le jeu existe déjà, utiliser l'instance existante
            $game = $existingGame;
        }

        // Vérifier si le jeu est déjà dans la liste de favoris
        if ($favoris->getGames()->contains($game)) {
            // Si le jeu est déjà dans la liste, le retirer
            $favoris->removeGame($game);
        } else {
            // Sinon, ajouter le jeu à la liste de favoris
            $favoris->addGame($game);
        }

        // Persister la liste mise à jour
        $manager->persist($favoris);
        $manager->flush();

        // Rendre une vue Twig avec la liste des jeux mis à jour
        return $this->redirectToRoute('profil', [
            'id' => $id,
            'favoris' => $favoris,
        ]);
    }
}
