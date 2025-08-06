<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'Member')]
    private ?Guild $guild = null;

    #[ORM\OneToOne(mappedBy: 'chef', cascade: ['persist', 'remove'])]
    private ?Guild $chiefOf = null;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'idClient')]
    private Collection $commandesPassees;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'idVendeur')]
    private Collection $commandesPrisesEnCharge;

    public function __construct()
    {
        $this->commandesPassees = new ArrayCollection();
        $this->commandesPrisesEnCharge = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Convert the roles array to a string.
     */
    public function getRolesAsString(): string
    {
        return implode(', ', $this->roles);
    }
    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getGuild(): ?Guild
    {
        return $this->guild;
    }

    public function setGuild(?Guild $guild): static
    {
        $this->guild = $guild;

        return $this;
    }

    public function getChiefOf(): ?Guild
    {
        return $this->chiefOf;
    }

    public function setChiefOf(?Guild $chiefOf): static
    {
        // unset the owning side of the relation if necessary
        if ($chiefOf === null && $this->chiefOf !== null) {
            $this->chiefOf->setChef(null);
        }

        // set the owning side of the relation if necessary
        if ($chiefOf !== null && $chiefOf->getChef() !== $this) {
            $chiefOf->setChef($this);
        }

        $this->chiefOf = $chiefOf;

        return $this;
    }

    public function quitGuild():static
    {
        $this->guild = null;
        if($this->getchiefOf() !== null && $this->chiefOf->getChef() === $this) {
            this->chiefOf->setChef(null);
        }
        return $this;
    }
    public function isChief(): bool
    {
        return $this->chiefOf !== null;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getcommandesPassees(): Collection
    {
        return $this->commandesPassees;
    }

    public function addCommandesPassEe(Commande $commandesPassEe): static
    {
        if (!$this->commandesPassees->contains($commandesPassEe)) {
            $this->commandesPassees->add($commandesPassEe);
            $commandesPassEe->setIdClient($this);
        }

        return $this;
    }

    public function removeCommandesPassEe(Commande $commandesPassEe): static
    {
        if ($this->commandesPassees->removeElement($commandesPassEe)) {
            // set the owning side to null (unless already changed)
            if ($commandesPassEe->getIdClient() === $this) {
                $commandesPassEe->setIdClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandesPrisesEnCharge(): Collection
    {
        return $this->commandesPrisesEnCharge;
    }

    public function addCommandesPrisesEnCharge(Commande $commandesPrisesEnCharge): static
    {
        if (!$this->commandesPrisesEnCharge->contains($commandesPrisesEnCharge)) {
            $this->commandesPrisesEnCharge->add($commandesPrisesEnCharge);
            $commandesPrisesEnCharge->setIdVendeur($this);
        }

        return $this;
    }

    public function removeCommandesPrisesEnCharge(Commande $commandesPrisesEnCharge): static
    {
        if ($this->commandesPrisesEnCharge->removeElement($commandesPrisesEnCharge)) {
            // set the owning side to null (unless already changed)
            if ($commandesPrisesEnCharge->getIdVendeur() === $this) {
                $commandesPrisesEnCharge->setIdVendeur(null);
            }
        }

        return $this;
    }
}
