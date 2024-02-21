<?php

namespace App\Entity;

use App\Repository\SecretRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: SecretRepository::class)]
#[ORM\Table(name:"secret")]
#[ORM\Index(name:"slug_idx", columns:["slug"])]
#[ORM\HasLifecycleCallbacks()]
class Secret implements EventLoggableInterface, JsonSerializable
{
    const DEFAULT_SECRET_TTL          = '7 day';
    const MAX_SECRET_TTL              = '1 year';

    // Event names
    const EVENT_CREATED               = 'created';
    const EVENT_READ_REQUESTED        = 'read.requested';
    const EVENT_READ_OK               = 'read.ok';
    const EVENT_READ_DENIED           = 'read.denied';
    const EVENT_DESTROYED             = 'destroyed';

    const DENIED_REASON_DESTROYED     = 'destroyed';
    const DENIED_REASON_EXPIRED       = 'expired';
    const DENIED_REASON_NOT_LOGGED_IN = 'not logged in';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(type: Types::STRING, length:7, nullable: true)]
    private ?string $slug;

    #[ORM\Column(type: Types::STRING, length:6000, nullable: true)]
    private ?string $data;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdOn = null;

    #[ORM\Column(type: Types::STRING, length:20, nullable: true)]
    private $iv;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $destroyedOn;

    #[ORM\ManyToOne(inversedBy: 'createdSecrets')]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'readSecrets')]
    private ?User $readBy = null;

    #[ORM\Column]
    private ?bool $mustBeLoggedInToRead = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiresOn = null;

    #[ORM\PrePersist]
    public function setCreatedOnValue()
    {
        $this->createdOn = new \DateTime();
        if ( !$this->expiresOn ) {
            $now = new \DateTimeImmutable();
            $this->expiresOn = $now->add( \DateInterval::createFromDateString( self::DEFAULT_SECRET_TTL ) );
        }
    }

    public function getEventLogPrefix(): string
    {
        return strtolower(substr(self::class,strrpos(self::class,'\\')+1));
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'createdOn' => $this->createdOn,
            'createdBy' => $this->createdBy->jsonSerialize(),
            'destroyedOn' => $this->destroyedOn,
            'description' => $this->description,
            'expiresOn' => $this->expiresOn,
            'readBy' => $this->readBy ? $this->readBy->jsonSerialize() : null,
            'mustBeLoggedInToRead' => $this->mustBeLoggedInToRead,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeInterface
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTimeInterface $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getIv(): ?string
    {
        return $this->iv;
    }

    public function setIv(?string $iv): self
    {
        $this->iv = $iv;

        return $this;
    }

    public function getDestroyedOn(): ?\DateTimeInterface
    {
        return $this->destroyedOn;
    }

    public function setDestroyedOn(?\DateTimeInterface $destroyedOn): self
    {
        $this->destroyedOn = $destroyedOn;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getReadBy(): ?User
    {
        return $this->readBy;
    }

    public function setReadBy(?User $readBy): static
    {
        $this->readBy = $readBy;

        return $this;
    }

    public function isMustBeLoggedInToRead(): ?bool
    {
        return $this->mustBeLoggedInToRead;
    }

    public function setMustBeLoggedInToRead(bool $mustBeLoggedInToRead): static
    {
        $this->mustBeLoggedInToRead = $mustBeLoggedInToRead;

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

    public function getExpiresOn(): ?\DateTimeImmutable
    {
        return $this->expiresOn;
    }

    public function setExpiresOn(\DateTimeImmutable $expiresOn): static
    {
        $this->expiresOn = $expiresOn;

        return $this;
    }
}
