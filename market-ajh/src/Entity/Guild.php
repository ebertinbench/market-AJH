<?php

namespace App\Entity;

use App\Repository\GuildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuildRepository::class)]
class Guild
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'guild')]
    private Collection $Member;

    #[ORM\OneToOne(inversedBy: 'chiefOf', cascade: ['persist', 'remove'])]
    private ?User $chef = null;

    #[ORM\Column]
    private ?bool $allowedToSell = null;

    public function __construct(?string $name = null, ?bool $allowedToSell = null)
    {
        $this->Member = new ArrayCollection();
        if ($name !== null) {
            $this->Name = $name;
        }
        if ($allowedToSell !== null) {
            $this->allowedToSell = $allowedToSell;
        }
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMember(): Collection
    {
        return $this->Member;
    }

    public function addMember(User $member): static
    {
        if (!$this->Member->contains($member)) {
            $this->Member->add($member);
            $member->setGuild($this);
        }

        return $this;
    }

    public function removeMember(User $member): static
    {
        if ($this->Member->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getGuild() === $this) {
                $member->setGuild(null);
            }
        }

        return $this;
    }

    public function getChef(): ?User
    {
        return $this->chef;
    }

    public function setChef(?User $chef): static
{
    if ($chef !== null) {
        // S'assurer que le chef est bien membre de la guilde
        if (!$this->Member->contains($chef)) {
            $this->addMember($chef); // Cela appellera setGuild() sur l'utilisateur aussi
        }
    }

    $this->chef = $chef;
    return $this;
}

    public function isAllowedToSell(): ?bool
    {
        return $this->allowedToSell;
    }

    public function setAllowedToSell(bool $allowedToSell): static
    {
        $this->allowedToSell = $allowedToSell;

        return $this;
    }
}
