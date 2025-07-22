<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?int $palier = null;

    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true, options: ["default" => 0])]
    private ?float $prix = 0;

    /**
     * @var Collection<int, GuildItems>
     */
    #[ORM\OneToMany(targetEntity: GuildItems::class, mappedBy: 'item')]
    private Collection $guildItems;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = "images/items/" . $image ;

        return $this;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPalier(): ?int
    {
        return $this->palier;
    }

    public function setPalier(?int $palier): static
    {
        $this->palier = $palier;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }


    public function __construct(
    ?string $image = null,
    ?int $palier = null,
    ?string $description = null,
    ?string $nom = null,
) {
    $this->image = $image ? "images/items/" . $image : null;
    $this->palier = $palier;
    $this->description = $description;
    $this->nom = $nom;
    $this->guildItems = new ArrayCollection();
}

    /**
     * @return Collection<int, GuildItems>
     */
    public function getGuildItems(): Collection
    {
        return $this->guildItems;
    }

    public function addGuildItem(GuildItems $guildItem): static
    {
        if (!$this->guildItems->contains($guildItem)) {
            $this->guildItems->add($guildItem);
            $guildItem->setItem($this);
        }

        return $this;
    }

    public function removeGuildItem(GuildItems $guildItem): static
    {
        if ($this->guildItems->removeElement($guildItem)) {
            // set the owning side to null (unless already changed)
            if ($guildItem->getItem() === $this) {
                $guildItem->setItem(null);
            }
        }

        return $this;
    }
}
