<?php
// src/Repository/CommandeRepository.php
namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Retourne les commandes filtrées par joueur et/ou statut.
     */
    /**
     * Retourne les commandes filtrées par joueur et/ou statut parmi les statuts autorisés.
     * @param int|null $playerId
     * @param string|null $status
     * @return Commande[]
     */
    public function findByFilters(?int $playerId, ?string $status, ?int $sellerId = null): array
    {
        $allowedStatuses = ['En attente', 'En attente de livraison', 'Livrée', 'Avortée'];
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.idClient', 'u')
            ->addSelect('u');

        if ($playerId !== null && $playerId !== '') {
            $qb->andWhere('u.id = :player')
                ->setParameter('player', $playerId);
        }

        if ($status && in_array($status, $allowedStatuses, true)) {
            $qb->andWhere('c.statut = :status')
                ->setParameter('status', $status);
        }

        if ($sellerId !== null && $sellerId !== '') {
            $qb->andWhere('c.idVendeur = :seller')
                ->setParameter('seller', $sellerId);
        }

        return $qb
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
