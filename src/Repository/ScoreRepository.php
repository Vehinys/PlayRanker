<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\Score;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Score>
 */
class ScoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Score::class);
    }

        public function getAverageScoreForGame(Game $game): float
    {
        $result = $this->createQueryBuilder('s')
            ->select('AVG(s.note) as average')
            ->where('s.game = :game')
            ->setParameter('game', $game)
            ->getQuery()
            ->getSingleScalarResult();

        return round($result, 2);
    }

    public function getAverageScoreForGameAndUser(Game $game, User $user): float
    {
        $result = $this->createQueryBuilder('s')
            ->select('AVG(s.note) as average')
            ->where('s.game = :game')
            ->andWhere('s.user = :user')
            ->setParameter('game', $game)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return round($result, 2);
    }
    
    public function findTopGames(int $limit)
    {
        return $this->createQueryBuilder('s')  // 's' est l'entité Score
            ->select('g.id', 'g.name', 'AVG(s.note) as avgNote')  // Sélectionnez les colonnes de 'g' et la moyenne des notes
            ->join('s.game', 'g')  // Joignez l'entité 'game'
            ->groupBy('g.id')  // Groupez par l'id du jeu
            ->orderBy('avgNote', 'DESC')  // Ordonnez par la moyenne des notes
            ->setMaxResults($limit)  // Limitez le nombre de résultats
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Score[] Returns an array of Score objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Score
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
