<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandesPassees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $idClient = null;

    #[ORM\ManyToOne(inversedBy: 'commandesPrisesEnCharge')]
    private ?User $idVendeur = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $dateCommande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $datePriseEnCharge = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateLivraison = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateAvortement = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GuildItems $idItem = null;

    #[ORM\Column(nullable: true)]
    private ?bool $traitementCompta = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdClient(): ?User
    {
        return $this->idClient;
    }

    public function setIdClient(?User $idClient): static
    {
        $this->idClient = $idClient;

        return $this;
    }

    public function getIdVendeur(): ?User
    {
        return $this->idVendeur;
    }

    public function setIdVendeur(?User $idVendeur): static
    {
        $this->idVendeur = $idVendeur;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateCommande(): ?\DateTime
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTime $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getDatePriseEnCharge(): ?\DateTime
    {
        return $this->datePriseEnCharge;
    }

    public function setDatePriseEnCharge(?\DateTime $datePriseEnCharge): static
    {
        $this->datePriseEnCharge = $datePriseEnCharge;

        return $this;
    }

    public function getDateLivraison(): ?\DateTime
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTime $dateLivraison): static
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getDateAvortement(): ?\DateTime
    {
        return $this->dateAvortement;
    }

    public function setDateAvortement(?\DateTime $dateAvortement): static
    {
        $this->dateAvortement = $dateAvortement;

        return $this;
    }

    public function getIdItem(): ?GuildItems
    {
        return $this->idItem;
    }

    public function setIdItem(?GuildItems $idItem): static
    {
        $this->idItem = $idItem;

        return $this;
    }

    public function __construct(
        ?User $idClient = null,
        ?User $idVendeur = null,
        ?int $quantite = null,
        ?string $statut = null,
        ?\DateTime $dateCommande = null,
        ?\DateTime $datePriseEnCharge = null,
        ?\DateTime $dateLivraison = null,
        ?\DateTime $dateAvortement = null,
        ?GuildItems $idItem = null
    ) {
        $this->idClient = $idClient;
        $this->idVendeur = $idVendeur;
        $this->quantite = $quantite;
        $this->statut = $statut;
        $this->dateCommande = $dateCommande;
        $this->datePriseEnCharge = $datePriseEnCharge;
        $this->dateLivraison = $dateLivraison;
        $this->dateAvortement = $dateAvortement;
        $this->idItem = $idItem;
        $this->setTraitementCompta(false);
    }

    public function getTotal(): ?int
    {
        if ($this->idItem && $this->quantite) {
            return $this->idItem->getPrice() * $this->quantite;
        }
        return null;
    }

    public function getGuild(): ?Guild
    {
        return $this->idItem ? $this->idItem->getGuild() : null;
    }

    public function isTraitementCompta(): ?bool
    {
        return $this->traitementCompta;
    }

    public function setTraitementCompta(?bool $traitementCompta): static
    {
        $this->traitementCompta = $traitementCompta;

        return $this;
    }
}
