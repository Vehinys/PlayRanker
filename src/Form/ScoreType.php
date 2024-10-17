<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\Score;
use App\Entity\RatingCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('note', IntegerType::class, [
            'constraints' => new Range(['min' => 1, 'max' => 10]),
            'attr' => ['min' => 1, 'max' => 10],
            'label' => 'Note (1 à 10)',
        ])
        ->add('ratingCategory', EntityType::class, [
            'class' => RatingCategory::class,
            'choice_label' => 'name', // Utiliser le nom de la catégorie
            'label' => 'Catégorie de la note',
        ])
        ->add('user', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'username', // Utiliser le nom d'utilisateur
            'label' => 'Utilisateur',
        ])
        ->add('game', EntityType::class, [
            'class' => Game::class,
            'choice_label' => 'title', // Utiliser le titre du jeu
            'label' => 'Jeu',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Score::class,
        ]);
    }
}
