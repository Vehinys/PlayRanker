<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
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
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('username', TextType::class, [
                'label' => 'username',
                'attr'  => [
                    'placeholder' => 'username',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un username.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le username doit contenir au moins {{ limit }} lettres.',
                        'maxMessage' => 'Le username ne peut pas dépasser {{ limit }} lettres.',
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
                        'message' => 'Please enter a password.',
                    ]),
                    new Length([
                        'min' => 12,
                        'max' => 255,
                        'minMessage' => 'The password must be at least {{ limit }} characters long.',
                        'maxMessage' => 'The password cannot exceed {{ limit }} characters.',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/',
                        'message' => 'The password must be at least 12 characters long and contain at least 
                        one uppercase letter, one lowercase letter, one number, and one special character.',
                    ]),
                ],
                'mapped' => false,
                'first_options' => [
                    'label' => 'Your password',
                    'attr' => [
                        'placeholder' => 'Password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirm your password',
                    'attr' => [
                        'placeholder' => 'Password',
                    ],
                ],
                'invalid_message' => 'The passwords must match.',
            ])
            
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'contact',
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
