<?php

namespace App\Entity;

use App\Enum\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'news')]
    private ?User $Emetteur = null;

    #[ORM\Column(length: 2000)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(enumType: NewsType::class)]
    private ?NewsType $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEmetteur(): ?User
    {
        return $this->Emetteur;
    }

    public function setEmetteur(?User $Emetteur): static
    {
        $this->Emetteur = $Emetteur;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
    public function __construct(?User $Emetteur = null, ?string $content = null, ?\DateTime $dateCreation = null, ?string $titre = null)
    {
        $this->Emetteur = $Emetteur;
        $this->content = $content;
        $this->dateCreation = $dateCreation;
        $this->titre = $titre;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getType(): ?NewsType
    {
        return $this->type;
    }

    public function setType(NewsType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
