<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr'  => [
                    'placeholder' => 'Pseudo',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un pseudo.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le pseudo doit contenir au moins {{ limit }} lettres.',
                        'maxMessage' => 'Le pseudo ne peut pas dépasser {{ limit }} lettres.',
                    ]),
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
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Accepter les conditions',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [
                    new NotBlank([ 
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                    new Length([
                        'min' => 12,    
                        'max' => 255,
                        'minMessage' => 'Le mot de passe doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/',
                        'message' => 'Le mot de passe doit comporter au moins 12 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.',
                    ]), 
                ],
                'mapped' => false,
                'first_options' => [
                    'label' => 'Votre mot de passe',
                    'attr' => [
                        'placeholder' => 'Mot de passe',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmez votre mot de passe',
                    'attr' => [
                        'placeholder' => 'Mot de passe',
                    ],
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
