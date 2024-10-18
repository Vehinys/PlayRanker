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
            'attr' => ['placeholder' => 'Email'],
            'constraints' => [
                new NotBlank(['message' => 'Veuillez entrer une adresse email.'])
            ],
        ])
            ->add('pseudo', TextType::class, [
                'label' => 'new Pseudo',
                'attr' => ['placeholder' => 'Entrez votre nouveau pseudo'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un pseudo.']),
                    new Length(['min' => 2, 'max' => 100]),
                ],
            ])
            ->add('avatar', UrlType::class, [
                'label' => 'Avatar',
                'attr' => ['placeholder' => 'Url de l\'avatar'],
            ])
            ->add('GamerTagPlaystation',TextType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Email'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email.'])
                ],
            ])
            ->add('GamerTagXbox',TextType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Email'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email.'])
                ],
            ])
            ->add('Youtube',TextType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Email'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email.'])
                ],
            ])
            ->add('Twitch',TextType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Email'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email.'])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
