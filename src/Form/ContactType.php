<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'attr' => [
                'placeholder' => 'Enter your email'
            ],
            'label' => 'Email',
            'constraints' => [
                new Assert\Email(),
                new Assert\NotBlank(),
            ]
        ])
        ->add('subject', TextType::class, [
            'label' => 'Subject',
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ])
        ->add('content', TextareaType::class, [
            'label' => 'Your Message:',
            'constraints' => [
                new Assert\NotBlank(),
            ],
            'attr' => [
                'placeholder' => 'Type your message here...',
                'rows' => 5,
            ],
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Send'
        ])
        ->add('captcha', Recaptcha3Type::class, [
            'constraints' => new Recaptcha3(),
            'action_name' => 'contact',
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
