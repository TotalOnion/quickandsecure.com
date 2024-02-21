<?php

namespace App\Entity;

use App\Repository\EmailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmailRepository::class)]
class Email implements EventLoggableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $identifier = null;

    #[ORM\ManyToOne(inversedBy: 'sentEmails')]
    private ?User $sendingUser = null;

    #[ORM\ManyToOne(inversedBy: 'receivedEmails')]
    private ?User $recipientUser = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    public function getEventLogPrefix(): string
    {
        return strtolower(substr(self::class,strrpos(self::class,'\\')+1));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?Uuid
    {
        return $this->identifier;
    }

    public function setIdentifier(Uuid $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getSendingUser(): ?User
    {
        return $this->sendingUser;
    }

    public function setSendingUser(?User $sendingUser): static
    {
        $this->sendingUser = $sendingUser;

        return $this;
    }

    public function getRecipientUser(): ?User
    {
        return $this->recipientUser;
    }

    public function setRecipientUser(?User $recipientUser): static
    {
        $this->recipientUser = $recipientUser;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
