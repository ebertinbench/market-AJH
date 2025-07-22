<?php

namespace App\Repository;

use App\Entity\Guild;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guild>
 */
class GuildRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guild::class);
    }

    //    /**
    //     * @return Guild[] Returns an array of Guild objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Guild
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
 * Retourne les guildes dont allowedToSell = true
 *
 * @return Guild[]
 */
public function findAllowedToSell(): array
{
    return $this->createQueryBuilder('g')
        ->andWhere('g.allowedToSell = :flag')
        ->setParameter('flag', true)
        ->orderBy('g.Name', 'ASC') // optionnel, tri par nom
        ->getQuery()
        ->getResult();
}
}
