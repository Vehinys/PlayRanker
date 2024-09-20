<?php

namespace App\Repository;

use App\Entity\SousCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;



    class SousCategoryRepository extends ServiceEntityRepository
    {

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Construit une nouvelle instance de la classe SousCategoryRepository.
     *
     * @param ManagerRegistry $registry Le registre Doctrine utilisé pour gérer le repository.
     */

        public function __construct(ManagerRegistry $registry)
        {
            parent::__construct($registry, SousCategory::class);
        }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Trouve toutes les entités SousCategory ayant l'ID spécifié.
     *
     * @param int $sousCategoryId L'ID de la SousCategory à trouver.
     * @return SousCategory[] Un tableau d'entités SousCategory correspondant à l'ID spécifié.
     */

        public function findBySousCategory(int $sousCategoryId): array
        {
        return $this->createQueryBuilder('g')
            ->join('g.sousCategories', 'sc')
            ->andWhere('sc.id = :sousCategoryId')
            ->setParameter('sousCategoryId', $sousCategoryId)
            ->getQuery()
            ->getResult();
        }
}
