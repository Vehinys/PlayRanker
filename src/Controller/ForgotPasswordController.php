<?php

namespace App\Controller;

use App\Form\ForgotPasswordType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SendEmail; // Assurez-vous que SendEmail est correctement importé

class ForgotPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    #[Route('/forgot/password', name: 'forgot_password', methods: ['POST', 'GET'])]
    public function sendRecoveryLink(
        Request $request,
        SendEmail $sendEmail,
        TokenGeneratorInterface $tokenGenerator
    ): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData(); // Correction ici

            $user = $this->userRepository->findOneBy([
                'email' => $email
            ]);

            if (!$user) {

                // Message de succès à afficher à l'utilisateur
                $this->addFlash('success', 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.');
                return $this->redirectToRoute('login');
            } 

            $user->setForgotPasswordToken($tokenGenerator->generateToken())
                 ->setForgotPasswordTokenResquestedAt(new \DateTimeImmutable('now'))
                 ->setForgotPasswordMustBeVerifiedBefore(new \DateTimeImmutable('+15 Minutes'));

            $this->entityManager->flush();

            $sendEmail->send([
                'recipent_email' => $user->getEmail(),
                'subject'        => 'Modification de vore mot de passe', 
                'html_template'  => 'pages/forgot_password/index.html.twig',
                'context'        => [
                    'user' => $user
                ]
            ]);

            $this->addFlash('success', 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.');
            return $this->redirectToRoute('login');
        }

        return $this->render('pages/forgot_password/index.html.twig', [
            'controller_name' => 'ForgotPasswordController',
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
    }
}
