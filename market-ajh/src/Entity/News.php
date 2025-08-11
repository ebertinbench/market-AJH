<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use Doctrine\ORM\Mapping as ORM;

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
    public function __construct(?User $Emetteur = null, ?string $content = null)
    {
        $this->Emetteur = $Emetteur;
        $this->content = $content;
    }
}
