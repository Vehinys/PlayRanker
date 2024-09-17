<?php

/**
 * This namespace contains the application's controllers.
 * Controllers are responsible for handling incoming HTTP requests and returning appropriate responses.
 */
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

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

        #[Route('/profil/editProfil', name: 'edit_profile')]
        public function editProfile(Request $request, EntityManagerInterface $entityManager): Response {

        $user = $this->getUser();

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
}
