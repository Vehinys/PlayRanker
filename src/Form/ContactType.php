<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Your pseudo:',
                'attr' => [
                    'class' => 'form-input', 
                    'placeholder' => 'Enter your pseudo'
                ],
            ])
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
            ->add('message', TextareaType::class, [
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
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}

