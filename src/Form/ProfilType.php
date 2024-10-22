<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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

        ->add('avatar', UrlType::class, [
            'label' => 'Avatar',
            'attr' => ['placeholder' => 'Avatar URL'],
        ])

        ->add('gamerTagPlaystation', TextType::class, [
            'label' => 'PlayStation Gamer Tag',
            'attr' => ['placeholder' => 'Your PlayStation Gamer Tag'],
        ])

        ->add('gamerTagXbox', TextType::class, [
            'label' => 'Xbox Gamer Tag',
            'attr' => ['placeholder' => 'Your Xbox Gamer Tag'],
        ])

        ->add('youtube', TextType::class, [
            'label' => 'YouTube',
            'attr' => ['placeholder' => 'Your lien YouTube'],
        ])

        ->add('twitch', TextType::class, [
            'label' => 'Twitch',
            'attr' => ['placeholder' => 'Your lien Twitch'],
        ]);
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
