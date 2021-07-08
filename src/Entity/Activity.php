<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Activity
 *
 * @ORM\Table(name="activity")
 * @ORM\Entity(repositoryClass=ActivityRepository::class)
 */
class Activity
{
    /**
     * @var string
     *
     * @ORM\Column(name="blocker", type="string", length=100, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $blocker;

    /**
     * @var string
     *
     * @ORM\Column(name="blockee", type="string", length=100, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $blockee;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';


    public function getBlocker(): string
    {
        return $this->blocker;
    }

    public function setBlocker(string $blocker): void
    {
        $this->blocker = $blocker;
    }

    public function getBlockee(): string
    {
        return $this->blockee;
    }

    public function setBlockee(string $blockee): self
    {
        $this->blockee = $blockee;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }


}
