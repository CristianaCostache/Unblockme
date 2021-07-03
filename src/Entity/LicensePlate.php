<?php

namespace App\Entity;

use App\Repository\LicensePlateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LicensePlateRepository::class)
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
