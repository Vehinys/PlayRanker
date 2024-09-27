<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Topic>
 */
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    /**
     * @return Topic[] Returns an array of Topic objects
     */
    public function findTopicsByCategory($categoryId): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.categoryForum', 'e')
            ->innerJoin('c.user', 'u')
            ->addSelect('u.pseudo')
            ->andWhere('c.categoryForum = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    public function findOneBySomeField($value): ?Topic
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
