<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        ->add('email', EmailType::class, [
            'label' => 'Email',
            'attr' => ['placeholder' => 'Enter your email'],
            'constraints' => [
                new NotBlank(['message' => 'Please enter an email address.'])
            ],
        ])

        ->add('username', TextType::class, [
            'label' => 'username',
            'attr' => ['placeholder' => 'Enter your new username'],
            'constraints' => [
                new NotBlank(['message' => 'Please enter a username.']),
                new Length(['min' => 2, 'max' => 100]),
            ],
        ])

        ->add('avatar', FileType::class, [
            'label' => 'Avatar (JPEG, PNG file)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG)',
                ])
            ],
        ])

        ->add('gamerTagPlaystation', TextType::class, [
            'label' => 'PlayStation Gamer Tag',
            'attr' => ['placeholder' => 'Your PlayStation Gamer Tag'],
            'required' => false,
        ])

        ->add('gamerTagXbox', TextType::class, [
            'label' => 'Xbox Gamer Tag',
            'attr' => ['placeholder' => 'Your Xbox Gamer Tag'],
            'required' => false,
        ])

        ->add('youtube', TextType::class, [
            'label' => 'YouTube',
            'attr' => ['placeholder' => 'Your lien YouTube'],
            'required' => false,
        ])

        ->add('twitch', TextType::class, [
            'label' => 'Twitch',
            'attr' => ['placeholder' => 'Your lien Twitch'],
            'required' => false,
        ]);
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
