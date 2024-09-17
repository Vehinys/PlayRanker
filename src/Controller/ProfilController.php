<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('pages/profil/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/profil/editProfil', name: 'edit_profile')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response {

    // Supposons que vous avez un moyen d'obtenir l'utilisateur connecté

    $user = $this->getUser();

    // Création d'un formulaire pour éditer le pseudo

    $form = $this->createFormBuilder($user)
        ->add('pseudo', TextType::class, [
            'label' => 'Nouveau Pseudo',
            'attr' => [
                'placeholder' => 'Entrez votre nouveau pseudo',
            ],
            'constraints' => [
                new NotBlank(['message' => 'Veuillez entrer un pseudo.']),
                new Length  (['min' => 2, 'max' => 100]),
                new Regex   (['pattern' => '/^[a-zA-Z]{2,100}$/', 'message' => 'Le pseudo ne doit contenir que des lettres.']),
            ],
        ])
        ->add('email', EmailType::class, [ 
            'label' => 'Email',
            'attr' => [
                'placeholder' => 'Email',
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer une adresse email.',
                ]),
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

}
