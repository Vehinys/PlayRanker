<?php

/**
 * This namespace contains the application's controllers.
 * Controllers are responsible for handling incoming HTTP requests and returning appropriate responses.
 */
namespace App\Controller;

use App\Entity\Game;
use App\Entity\GamesList;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\GamesListRepository;
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

    /**
     * Rend la page de profil de l'utilisateur.
     *
     * Cette action récupère l'utilisateur actuellement authentifié et le transmet au
     * Modèle 'pages/profil/index.html.twig' pour le rendu.
     *
     * @return Response La page de profil rendue.
     */

        #[Route('/profil', name: 'profil')]
        public function index(): Response
        {
            $user = $this->getUser();

            return $this->render('pages/profil/index.html.twig', [
                'user' => $user
            ]);
        }
        
    /**
     * Gère la modification des informations de profil de l'utilisateur, y compris son pseudo, son e-mail et son avatar.
     *
     * Cette action crée un formulaire permettant à l'utilisateur de mettre à jour ses informations de profil, valide les données du formulaire,
     * puis conserve les modifications apportées à la base de données à l'aide de l'EntityManagerInterface fournie.
     *
     * @param Request $request La requête HTTP actuelle.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @return Response La page d'édition du profil rendue.
     */
        #[Route('/profil/editProfil', name: 'edit_profile')]
        public function editProfile(Request $request, EntityManagerInterface $entityManager): Response {

        $user = $this->getUser();


        $form = $this->createFormBuilder($user)
            ->add('pseudo', TextType::class, [
                'label' => 'Nouveau Pseudo',
                'attr' => [
                    'placeholder' => 'Entrez votre nouveau pseudo',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un pseudo.']),
                    new Length  (['min' => 2, 'max' => 100]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                ],
                'constraints' => [
                    new NotBlank([ 'message' => 'Veuillez entrer une adresse email.'])
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

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    
    #[Route('/profil/delete', name: 'delete_profile')]
    public function deleteProfile(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
    ): Response {
        // Récupérer l'utilisateur courant
        $user = $this->getUser();
    
        // Vérifier que l'utilisateur est bien authentifié et est une instance de User
        if (!$user instanceof User) {
            throw new \Exception('The user is invalid');
        }
    
        // Rechercher l'id de la gameList de l'utilisateur
        $gameList = $entityManager->getRepository(GamesList::class)->findOneBy(['user' => $user]);
    
        // Anonymiser l'utilisateur
        $user->anonymize($this->passwordHasher);
    
        // Enregistrer les changements (anonymisation)
        $entityManager->flush();
    
        // Vérifier si une liste de jeux a été trouvée
        if ($gameList) {
            // Supprimer la gameList de l'utilisateur
            $entityManager->remove($gameList);
            // Enregistrer les changements après la suppression
            $entityManager->flush();
        }
    
        // Déconnecter l'utilisateur
        $tokenStorage->setToken(null);
        $session->invalidate();
        
        // Rediriger vers la page d'inscription
        return $this->redirectToRoute('register');
    }
    
    
    #[Route('/game/favoris/{gameId}', name: 'games_favoris')]
    public function addFavoris(

        int $gameId,
        GamesListRepository $repository,
        Request $request,
        EntityManagerInterface $manager,
        GameRepository $gameRepository

    ): Response {

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
    
        // Récupérer la liste de favoris de l'utilisateur
        $gameList = $repository->findOneBy(['user' => $user, 'name' => 'Favoris']);
    
        // Vérifier si le jeu existe déjà dans la base de données
        $existingGame = $gameRepository->findOneBy(['id_game_api' => $gameId]);
    
        // Si le jeu n'existe pas, créer une nouvelle instance de Game
        if (!$existingGame) {
            $game = new Game();
            $game->setIdGameApi($gameId);
    
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
        if ($gameList->getGames()->contains($game)) {
            // Si le jeu est déjà dans la liste, le retirer
            $gameList->removeGame($game);
        } else {
            // Sinon, ajouter le jeu à la liste de favoris
            $gameList->addGame($game);
        }
    
        // Persister la liste mise à jour
        $manager->persist($gameList);
        $manager->flush();
    
        // Rendre une vue Twig avec la liste des jeux mis à jour
        return $this->redirectToRoute('detail_jeu', ['id' => $gameId]);
    }
    

}
