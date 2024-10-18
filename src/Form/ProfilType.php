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
        ->add('pseudo', TextType::class, [
            'label' => 'New Pseudo',
            'attr' => ['placeholder' => 'Enter your new pseudo'],
            'constraints' => [
                new NotBlank(['message' => 'Please enter a pseudo.']),
                new Length(['min' => 2, 'max' => 100]),
            ],
        ])
        ->add('avatar', UrlType::class, [
            'label' => 'Avatar',
            'attr' => ['placeholder' => 'Avatar URL'],
        ])
        ->add('GamerTagPlaystation', TextType::class, [
            'label' => 'PlayStation Gamer Tag',
            'attr' => ['placeholder' => 'Enter your PlayStation Gamer Tag'],
        ])
        ->add('GamerTagXbox', TextType::class, [
            'label' => 'Xbox Gamer Tag',
            'attr' => ['placeholder' => 'Enter your Xbox Gamer Tag'],
        ])
        ->add('Youtube', TextType::class, [
            'label' => 'YouTube',
            'attr' => ['placeholder' => 'Enter your YouTube channel'],
        ])
        ->add('Twitch', TextType::class, [
            'label' => 'Twitch',
            'attr' => ['placeholder' => 'Enter your Twitch username'],
        ]);
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
