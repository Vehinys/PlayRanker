<?php

namespace App\Form;

use App\Entity\RatingCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RatingCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Category Rating Name',
                'attr' => [
                    'class' => 'form-control', 
                    'placeholder' => 'Enter the Rating Category name'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name for the Rating category.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 150,
                        'minMessage' => 'The Rating category name must be at least {{ limit }} characters long',
                        'maxMessage' => 'The Rating category name cannot be longer than {{ limit }} characters',
                    ]),
                ],
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
