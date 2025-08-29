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

    /**
     * @var Collection<int, News>
     */
    #[ORM\OneToMany(targetEntity: News::class, mappedBy: 'Emetteur')]
    private Collection $news;

    /**
     * @var Collection<int, AvisCommande>
     */
    #[ORM\OneToMany(targetEntity: AvisCommande::class, mappedBy: 'idClient')]
    private Collection $avisdonnes;

    /**
     * @var Collection<int, AvisCommande>
     */
    #[ORM\OneToMany(targetEntity: AvisCommande::class, mappedBy: 'idVendeur')]
    private Collection $avisrecus;

    #[ORM\Column(length: 255)]
    private ?string $wallpaper = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pseudoMinecraft = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pseudoDiscord = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender')]
    private Collection $messagesSent;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'recipient')]
    private Collection $messagesReceived;

    public function __construct()
    {
        $this->commandesPassees = new ArrayCollection();
        $this->commandesPrisesEnCharge = new ArrayCollection();
        $this->news = new ArrayCollection();
        $this->avisdonnes = new ArrayCollection();
        $this->avisrecus = new ArrayCollection();
        $this->messagesSent = new ArrayCollection();
        $this->messagesReceived = new ArrayCollection();
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
            $this->chiefOf->setChef(null);
        }
        // Retirer les rôles vendeur et comptable quand on quitte une guilde
        $this->removeVendeurRole();
        $this->removeComptableRole();
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

    /**
     * @return Collection<int, News>
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): static
    {
        if (!$this->news->contains($news)) {
            $this->news->add($news);
            $news->setEmetteur($this);
        }

        return $this;
    }

    public function removeNews(News $news): static
    {
        if ($this->news->removeElement($news)) {
            // set the owning side to null (unless already changed)
            if ($news->getEmetteur() === $this) {
                $news->setEmetteur(null);
            }
        }

        return $this;
    }

    public function getBestRole(): ?string
    {
        $hierarchy = [
            'ROLE_ADMIN' => ['ROLE_COMPTABLE', 'ROLE_VENDEUR', 'ROLE_CLIENT'],
            'ROLE_COMPTABLE' => ['ROLE_VENDEUR', 'ROLE_CLIENT'],
            'ROLE_VENDEUR' => ['ROLE_CLIENT'],
            'ROLE_CLIENT' => []
        ];

        $roles = $this->getRoles();
        $bestRole = null;

        foreach (array_keys($hierarchy) as $role) {
            if (in_array($role, $roles, true)) {
            $bestRole = $role;
            break;
            }
        }

        return match ($bestRole) {
            'ROLE_ADMIN' => 'Administrateur',
            'ROLE_COMPTABLE' => 'Comptable',
            'ROLE_VENDEUR' => 'Vendeur',
            'ROLE_CLIENT' => 'Client',
            default => null,
        };
    }

    /**
     * @return Collection<int, AvisCommande>
     */
    public function getAvisdonnes(): Collection
    {
        return $this->avisdonnes;
    }

    public function addAvisdonne(AvisCommande $avisdonne): static
    {
        if (!$this->avisdonnes->contains($avisdonne)) {
            $this->avisdonnes->add($avisdonne);
            $avisdonne->setIdClient($this);
        }

        return $this;
    }

    public function removeAvisdonne(AvisCommande $avisdonne): static
    {
        if ($this->avisdonnes->removeElement($avisdonne)) {
            // set the owning side to null (unless already changed)
            if ($avisdonne->getIdClient() === $this) {
                $avisdonne->setIdClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AvisCommande>
     */
    public function getAvisrecus(): Collection
    {
        return $this->avisrecus;
    }

    public function addAvisrecu(AvisCommande $avisrecu): static
    {
        if (!$this->avisrecus->contains($avisrecu)) {
            $this->avisrecus->add($avisrecu);
            $avisrecu->setIdVendeur($this);
        }

        return $this;
    }

    public function removeAvisrecu(AvisCommande $avisrecu): static
    {
        if ($this->avisrecus->removeElement($avisrecu)) {
            // set the owning side to null (unless already changed)
            if ($avisrecu->getIdVendeur() === $this) {
                $avisrecu->setIdVendeur(null);
            }
        }

        return $this;
    }

    public function getMoyenneAvisVendeur(): float
    {
        $total = 0;
        $count = 0;

        foreach ($this->avisrecus as $avis) {
            $total += $avis->getNote();
            $count++;
        }

        return $count > 0 ? $total / $count : 0;
    }

    public function getWallpaper(): ?string
    {
        return $this->wallpaper;
    }

    public function setWallpaper(string $wallpaper): static
    {
        $this->wallpaper = $wallpaper;

        return $this;
    }

    public function getPseudoMinecraft(): ?string
    {
        return $this->pseudoMinecraft;
    }

    public function setPseudoMinecraft(?string $pseudoMinecraft): static
    {
        $this->pseudoMinecraft = $pseudoMinecraft;

        return $this;
    }

    public function getPseudoDiscord(): ?string
    {
        return $this->pseudoDiscord;
    }

    public function setPseudoDiscord(?string $pseudoDiscord): static
    {
        $this->pseudoDiscord = $pseudoDiscord;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessagesSent(): Collection
    {
        return $this->messagesSent;
    }

    public function addMessagesSent(Message $messagesSent): static
    {
        if (!$this->messagesSent->contains($messagesSent)) {
            $this->messagesSent->add($messagesSent);
            $messagesSent->setSender($this);
        }

        return $this;
    }

    public function removeMessagesSent(Message $messagesSent): static
    {
        if ($this->messagesSent->removeElement($messagesSent)) {
            // set the owning side to null (unless already changed)
            if ($messagesSent->getSender() === $this) {
                $messagesSent->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessagesReceived(): Collection
    {
        return $this->messagesReceived;
    }

    public function addMessagesReceived(Message $messagesReceived): static
    {
        if (!$this->messagesReceived->contains($messagesReceived)) {
            $this->messagesReceived->add($messagesReceived);
            $messagesReceived->setRecipient($this);
        }

        return $this;
    }

    public function removeMessagesReceived(Message $messagesReceived): static
    {
        if ($this->messagesReceived->removeElement($messagesReceived)) {
            // set the owning side to null (unless already changed)
            if ($messagesReceived->getRecipient() === $this) {
                $messagesReceived->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * Get unread messages count
     */
    public function getUnreadMessagesCount(): int
    {
        return $this->messagesReceived->filter(function(Message $message) {
            return !$message->isRead();
        })->count();
    }

    /**
     * Add the ROLE_VENDEUR to user roles
     */
    public function addVendeurRole(): static
    {
        $roles = $this->roles;
        if (!in_array('ROLE_VENDEUR', $roles)) {
            $roles[] = 'ROLE_VENDEUR';
            $this->roles = $roles;
        }
        return $this;
    }

    /**
     * Remove the ROLE_VENDEUR from user roles
     */
    public function removeVendeurRole(): static
    {
        $roles = $this->roles;
        $index = array_search('ROLE_VENDEUR', $roles);
        if ($index !== false) {
            unset($roles[$index]);
            $this->roles = array_values($roles);
        }
        return $this;
    }

    /**
     * Check if user has ROLE_VENDEUR
     */
    public function hasVendeurRole(): bool
    {
        return in_array('ROLE_VENDEUR', $this->roles);
    }

    /**
     * Add the ROLE_COMPTABLE to user roles
     */
    public function addComptableRole(): static
    {
        $roles = $this->roles;
        if (!in_array('ROLE_COMPTABLE', $roles)) {
            $roles[] = 'ROLE_COMPTABLE';
            $this->roles = $roles;
        }
        return $this;
    }

    /**
     * Remove the ROLE_COMPTABLE from user roles
     */
    public function removeComptableRole(): static
    {
        $roles = $this->roles;
        $index = array_search('ROLE_COMPTABLE', $roles);
        if ($index !== false) {
            unset($roles[$index]);
            $this->roles = array_values($roles);
        }
        return $this;
    }

    /**
     * Check if user has ROLE_COMPTABLE
     */
    public function hasComptableRole(): bool
    {
        return in_array('ROLE_COMPTABLE', $this->roles);
    }
}
