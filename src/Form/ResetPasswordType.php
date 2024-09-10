<?php

namespace App\Form;

use App\Form\FormExtension\RepeatedPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    
    {
     $builder->add('password', RepeatedPasswordType::class);
    }

    public function configureOptions ( OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}