<?php

namespace App\Entity;

use App\Repository\SecretRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SecretRepository::class)
 * @ORM\Table(name="secret",indexes={@ORM\Index(name="slug_idx", columns={"slug"})})
 * @ORM\HasLifecycleCallbacks()
 */
class Secret
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=6000, nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdOn;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $iv;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $destroyedOn;

    /**
     * @ORM\PrePersist
     */
    public function setCreatedOnValue()
    {
        $this->createdOn = new \DateTime();
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
}
