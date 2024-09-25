<?php

namespace App\Repository;

use App\Entity\Platform;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Platform>
 */
class PlatformRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Platform::class);
    }

        /**
     * Trouve toutes les entités SousCategory ayant l'ID spécifié.
     *
     * @param int $sousCategoryId L'ID de la SousCategory à trouver.
     * @return platform[] Un tableau d'entités SousCategory correspondant à l'ID spécifié.
     */

    public function findByPlatform(int $platformId): array
    {
    return $this->createQueryBuilder('c')
        ->join('c.platform', 'cp')
        ->andWhere('cp.id = :platformId')
        ->setParameter('platformId', $platformId)
        ->getQuery()
        ->getResult();
    }
}
