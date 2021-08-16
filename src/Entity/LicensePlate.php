<?php

namespace App\Entity;

use App\Repository\LicensePlateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LicensePlateRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class LicensePlate
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $license_plate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="licensePlates")
     */
    private $user;

    /** @ORM\Column(name="created_at", type="string", length=255) */
    private $createdAt;

    /** @ORM\Column(name="updated_at", type="string", length=255) */
    private $updatedAt;

    /** @ORM\PreUpdate */
    public function doStuffOnPreUpdate()
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    /** @ORM\PrePersist */
    public function doStuffOnPrePersist()
    {
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return $this->license_plate;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLicensePlate(): ?string
    {
        return $this->license_plate;
    }

    public function setLicensePlate(string $license_plate): self
    {
        $this->license_plate = $license_plate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
