<?php

namespace App\Repository;

use App\Entity\GuildItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GuildItems>
 */
class GuildItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuildItems::class);
    }

    /**
     * Trouve tous les GuildItems pour un item donné, triés par prix croissant
     * @return GuildItems[]
     */
    public function findByItemOrderedByPrice(int $itemId): array
    {
        return $this->createQueryBuilder('gi')
            ->join('gi.item', 'i')
            ->join('gi.guild', 'g')
            ->andWhere('i.id = :itemId')
            ->andWhere('gi.miseEnVente = true')
            ->setParameter('itemId', $itemId)
            ->orderBy('gi.price', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return GuildItems[] Returns an array of GuildItems objects
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

//    public function findOneBySomeField($value): ?GuildItems
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
