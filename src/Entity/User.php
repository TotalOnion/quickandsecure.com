<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(
        message: 'The specified email is not valid.',
    )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Secret::class)]
    private Collection $secrets;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Secret::class)]
    private Collection $createdSecrets;

    #[ORM\OneToMany(mappedBy: 'readBy', targetEntity: Secret::class)]
    private Collection $readSecrets;

    #[ORM\Column(nullable: true)]
    private ?bool $emailValidated = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailValidationToken = null;

    #[ORM\OneToMany(mappedBy: 'sendingUser', targetEntity: Email::class)]
    private Collection $sentEmails;

    #[ORM\OneToMany(mappedBy: 'recipientUser', targetEntity: Email::class)]
    private Collection $receivedEmails;

    public function __construct()
    {
        $this->secrets = new ArrayCollection();
        $this->createdSecrets = new ArrayCollection();
        $this->readSecrets = new ArrayCollection();
        $this->sentEmails = new ArrayCollection();
        $this->receivedEmails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
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

    /**
     * @return Collection<int, Secret>
     */
    public function getSecrets(): Collection
    {
        return $this->secrets;
    }

    public function addSecret(Secret $secret): static
    {
        if (!$this->secrets->contains($secret)) {
            $this->secrets->add($secret);
            $secret->setCreatedBy($this);
        }

        return $this;
    }

    public function removeSecret(Secret $secret): static
    {
        if ($this->secrets->removeElement($secret)) {
            // set the owning side to null (unless already changed)
            if ($secret->getCreatedBy() === $this) {
                $secret->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Secret>
     */
    public function getCreatedSecrets(): Collection
    {
        return $this->createdSecrets;
    }

    public function addCreatedSecret(Secret $createdSecret): static
    {
        if (!$this->createdSecrets->contains($createdSecret)) {
            $this->createdSecrets->add($createdSecret);
            $createdSecret->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedSecret(Secret $createdSecret): static
    {
        if ($this->createdSecrets->removeElement($createdSecret)) {
            // set the owning side to null (unless already changed)
            if ($createdSecret->getCreatedBy() === $this) {
                $createdSecret->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Secret>
     */
    public function getReadSecrets(): Collection
    {
        return $this->readSecrets;
    }

    public function addReadSecret(Secret $readSecret): static
    {
        if (!$this->readSecrets->contains($readSecret)) {
            $this->readSecrets->add($readSecret);
            $readSecret->setReadBy($this);
        }

        return $this;
    }

    public function removeReadSecret(Secret $readSecret): static
    {
        if ($this->readSecrets->removeElement($readSecret)) {
            // set the owning side to null (unless already changed)
            if ($readSecret->getReadBy() === $this) {
                $readSecret->setReadBy(null);
            }
        }

        return $this;
    }

    public function isEmailValidated(): ?bool
    {
        return $this->emailValidated;
    }

    public function setEmailValidated(?bool $emailValidated): static
    {
        $this->emailValidated = $emailValidated;

        return $this;
    }

    public function getEmailValidationToken(): ?string
    {
        return $this->emailValidationToken;
    }

    public function setEmailValidationToken(?string $emailValidationToken): static
    {
        $this->emailValidationToken = $emailValidationToken;

        return $this;
    }

    /**
     * @return Collection<int, Email>
     */
    public function getSentEmails(): Collection
    {
        return $this->sentEmails;
    }

    public function addSentEmail(Email $sentEmail): static
    {
        if (!$this->sentEmails->contains($sentEmail)) {
            $this->sentEmails->add($sentEmail);
            $sentEmail->setSendingUser($this);
        }

        return $this;
    }

    public function removeSentEmail(Email $sentEmail): static
    {
        if ($this->sentEmails->removeElement($sentEmail)) {
            // set the owning side to null (unless already changed)
            if ($sentEmail->getSendingUser() === $this) {
                $sentEmail->setSendingUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Email>
     */
    public function getReceivedEmails(): Collection
    {
        return $this->receivedEmails;
    }

    public function addReceivedEmail(Email $receivedEmail): static
    {
        if (!$this->receivedEmails->contains($receivedEmail)) {
            $this->receivedEmails->add($receivedEmail);
            $receivedEmail->setRecipientUser($this);
        }

        return $this;
    }

    public function removeReceivedEmail(Email $receivedEmail): static
    {
        if ($this->receivedEmails->removeElement($receivedEmail)) {
            // set the owning side to null (unless already changed)
            if ($receivedEmail->getRecipientUser() === $this) {
                $receivedEmail->setRecipientUser(null);
            }
        }

        return $this;
    }
}
