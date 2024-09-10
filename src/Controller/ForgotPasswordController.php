<?php

namespace App\Controller;

use App\Form\ForgotPasswordType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface; // Utilisation de EntityManagerInterface
use Symfony\Component\Routing\Annotation\Route; // Utilisation de l'annotation pour la route

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

    #[Route('/forgot/password', name: 'forgot_password', method:['POST','GET'] )]

    public function sendRecoveryLink(
        Request $request,
        SendEmail $sendEmail,
        TokenGeneratorInterface $tokenGenerator
    ): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form-> handleRequest($request);



        return $this->render('pages/forgot_password/index.html.twig', [
            'controller_name' => 'ForgotPasswordController',
        ]);
    }
}
