<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Repository\ScoreRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('home');
    }

    #[Route('/accueil', name: 'home', methods: ['GET', 'POST'])]
    public function home(

        Request $request, 
        CommentRepository $commentRepository,
        ScoreRepository $scoreRepository,
        UserRepository $userRepository,
        CategoryRepository $categoryRepository,
        GameRepository $gameRepository,
        
    ): Response {

        $form = $this->createForm(ContactType::class); 
        $form->handleRequest($request);
    
        $comments = $commentRepository->findAll();
        
        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();
        
        // Récupérer les jeux avec les meilleures notes
        $topGames = $scoreRepository->findTopGames(3);
        
        $categories = $categoryRepository->findAll();
        
        return $this->render('pages/home/index.html.twig', [
            'contactForm' => $form->createView(),
            'comments' => $comments,
            'topGames' => $topGames,
            'categories'  => $categories,
            'users' => $users,
        ]);
    }

    #[Route('/accueil/contact', name: 'contact')]
    public function contact(

        Request $request, 
        MailerInterface $mailer,
        CommentRepository $commentRepository,
        ScoreRepository $scoreRepository
        
    ): Response {

        $comments = $commentRepository->findAll();
        $topGames = $scoreRepository->findTopGames(3);

        $contactForm = $this->createForm(ContactType::class);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $data = $contactForm->getData();

            $adress = $data['email'];
            $subject = $data['subject'];
            $content = $data['content'];

            $email = (new Email());
            $email  ->from($adress)
                    ->to('albert.lecomte1989@gmail.com')
                    ->subject($subject)
                    ->text($content);

            $mailer->send($email);

            return $this->redirectToRoute('home');
        }

        return $this->render('pages/home/index.html.twig', [
            'contactForm' => $contactForm->createView(),
            'comments' => $comments,
            'topGames' => $topGames,
        ]);
    }

    #[Route('/profil/{username}', name: 'lienProfile')]
    public function lienProfile(

        string $username,
        UserRepository $userRepository,
        TypeRepository $typeRepository

    ): Response {
        
        // Récupérer l'utilisateur connecté
        $currentUser = $this->getUser();
    
        // Rechercher l'utilisateur avec le username donné
        $user = $userRepository->findOneBy(['username' => $username]);
    
        // Vérifie si le profil existe
        if (!$user) {
            throw $this->createNotFoundException('Profil non trouvé');
        }
    
        // Vérifie si le username est "UserDelete"
        if ($username === 'UserDelete') {

            // Rediriger vers le profil de l'utilisateur connecté
            return $this->redirectToRoute('lienProfile', ['username' => $currentUser->getUsername()]);

        }
    
        // Récupération des listes de jeux associées à l'utilisateur cible
        $gamesLists = $user->getGamesLists();
    
        // Récupération de tous les types de listes
        $types = $typeRepository->findAll();
        
        // Rendre la vue avec les informations du profil
        return $this->render('pages/profil/index.html.twig', [
            'user' => $user,            // Passe l'utilisateur au template comme "user"
            'currentUser' => $currentUser, // Passe l'utilisateur connecté à la vue Twig
            'gamesLists' => $gamesLists,
            'types' => $types,
        ]);
    }
    
}