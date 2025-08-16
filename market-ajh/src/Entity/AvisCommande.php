<?php

namespace App\Entity;

use App\Repository\AvisCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisCommandeRepository::class)]
class AvisCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'avisdonnes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $idClient = null;

    #[ORM\ManyToOne(inversedBy: 'avisrecus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $idVendeur = null;

    #[ORM\Column]
    private ?int $note = null;

    #[ORM\OneToOne(inversedBy: 'avisCommande', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $idCommande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getIdCommande(): ?Commande
    {
        return $this->idCommande;
    }

    public function setIdCommande(Commande $idCommande): static
    {
        $this->idCommande = $idCommande;

        return $this;
    }
}
