<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('content', TextareaType::class, [
            'attr' => [
                'class' => 'w-full border border-gray-300 rounded-md p-4 focus:outline-none focus:ring-2 focus:ring-blue-500',
                'placeholder' => 'Post content',
                'rows' => 5,  // Nombre de lignes visibles
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}