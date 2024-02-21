<?php

namespace App\Entity;

use App\Repository\EventLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: EventLogRepository::class)]
#[ORM\Index(name:"associated_entity_id_idx", columns:["associated_entity_id"])]
#[ORM\Index(name:"entity_class_name_idx", columns:["entity_class_name"])]
#[ORM\Index(name:"event_idx", columns:["event"])]
#[ORM\Index(name:"timestamp_idx", columns:["timestamp"])]
#[ORM\HasLifecycleCallbacks()]
class EventLog implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $associatedEntityId = null;

    #[ORM\Column(length: 255)]
    private ?string $entityClassName = null;

    #[ORM\Column(length: 255)]
    private ?string $event = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $timestamp = null;

    #[ORM\Column(nullable: true)]
    private ?array $eventData = null;

    #[ORM\PrePersist]
    public function setTimestampValue()
    {
        $this->timestamp = new \DateTimeImmutable();
    }

    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->id,
            'timestamp' => $this->timestamp,
            'event'     => $this->event,
            'eventData' => $this->eventData
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssociatedEntityId(): ?int
    {
        return $this->associatedEntityId;
    }

    public function setAssociatedEntityId(int $associatedEntityId): static
    {
        $this->associatedEntityId = $associatedEntityId;

        return $this;
    }

    public function getEntityClassName(): ?string
    {
        return $this->entityClassName;
    }

    public function setEntityClassName(string $entityClassName): static
    {
        $this->entityClassName = $entityClassName;

        return $this;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setEvent(string $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getEventData(): ?array
    {
        return $this->eventData;
    }

    public function setEventData(?array $eventData): static
    {
        $this->eventData = $eventData;

        return $this;
    }
}
