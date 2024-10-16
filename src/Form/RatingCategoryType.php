<?php

namespace App\Form;

use App\Entity\RatingCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RatingCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Rating Category Name',
                'attr' => [
                    'maxlength' => 150,
                    'class' => 'form-control', 
                    'placeholder' => 'Enter the Rating category name'
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RatingCategory::class,
        ]);
    }
}
