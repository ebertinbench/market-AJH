<?php
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
     * Filtre les commandes par nom ou ID d'utilisateur (idClient) et/ou de guilde (via idItem.guild).
     * @param string|null $user Nom ou ID utilisateur
     * @param string|null $guild Nom ou ID guilde
     * @return Commande[]
     */
    public function findByUserAndGuild(?string $user, ?string $guild): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.idClient', 'u')
            ->leftJoin('c.idItem', 'gi')
            ->leftJoin('gi.guild', 'g')
            ->addSelect('u')
            ->addSelect('gi')
            ->addSelect('g');

        if ($user !== null && $user !== '') {
            $this->addUserFilter($qb, $user);
        }
        if ($guild !== null && $guild !== '') {
            $this->addGuildFilter($qb, $guild);
        }

        return $qb
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Ajoute un filtre utilisateur (id ou username) au QueryBuilder.
     */
    private function addUserFilter($qb, string $user): void
    {
        if (is_numeric($user)) {
            $qb->andWhere('u.id = :userId')
                ->setParameter('userId', (int)$user);
        } else {
            $qb->andWhere('u.username LIKE :userName')
                ->setParameter('userName', '%' . $user . '%');
        }
    }

    /**
     * Ajoute un filtre guilde (id ou nom) au QueryBuilder.
     */
    private function addGuildFilter($qb, string $guild): void
    {
        if (is_numeric($guild)) {
            $qb->andWhere('g.id = :guildId')
                ->setParameter('guildId', (int)$guild);
        } else {
            $qb->andWhere('g.Name LIKE :guildName')
                ->setParameter('guildName', '%' . $guild . '%');
        }
    }

    /**
     * Filtre les commandes par nom ou ID d'utilisateur (idClient) et/ou de guilde (via idItem.guild).
     * @param string|null $user Nom ou ID utilisateur
     * @param string|null $guild Nom ou ID guilde
     * @return Commande[]
     */
    public function findByUserAndGuildeAndStatutAndTraitementCompta(?string $user, ?string $guild, ?string $statut = null, ?string $traitementCompta = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.idClient', 'u')
            ->leftJoin('c.idItem', 'gi')
            ->leftJoin('gi.guild', 'g')
            ->addSelect('u')
            ->addSelect('gi')
            ->addSelect('g');

        if ($user !== null && $user !== '') {
            $this->addUserFilter($qb, $user);
        }
        if ($guild !== null && $guild !== '') {
            $this->addGuildFilter($qb, $guild);
        }
        if ($statut !== null && $statut !== '' && $statut !== 'Tous') {
            $qb->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut);
        }
        if ($traitementCompta !== null && $traitementCompta !== '' && $traitementCompta !== 'Tous') {
            $qb->andWhere('c.traitementCompta = :traitementCompta')
                ->setParameter('traitementCompta', $traitementCompta);
        }

        return $qb
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Ajoute un filtre utilisateur (id ou username) au QueryBuilder.
     */
    

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

    /**
     * Retourne les commandes filtrées par guilde.
     * @param string|null $guild Nom ou ID de la guilde
     * @return Commande[]
     */
    public function findByGuild(?string $guild): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.idItem', 'gi')
            ->leftJoin('gi.guild', 'g')
            ->addSelect('gi')
            ->addSelect('g');

        if ($guild !== null && $guild !== '') {
            $this->addGuildFilter($qb, $guild);
        }

        return $qb
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }


}
