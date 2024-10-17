<?php

namespace App\Form;

use App\Entity\Score;
use Symfony\Component\Form\AbstractType;
use App\Repository\RatingCategoryRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ratingCategoryRepository = $options['rating_category_repository'];
        $ratingCategories = $ratingCategoryRepository->findAll();
    
        foreach ($ratingCategories as $category) {
            $builder->add('rating' . $category->getId(), IntegerType::class, [
                'mapped' => false,
                'constraints' => new Range(['min' => 1, 'max' => 10]),
                'attr' => ['min' => 1, 'max' => 10],
                'label' => $category->getName() . ' (1 à 10)',
            ]);
        }

    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Score::class,
            'rating_category_repository' => null,
        ]);
    
        $resolver->setRequired('rating_category_repository');
        $resolver->setAllowedTypes('rating_category_repository', [RatingCategoryRepository::class]);
    }
}