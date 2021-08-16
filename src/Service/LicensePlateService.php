<?php


namespace App\Service;

use App\Entity\LicensePlate;
use App\Entity\User;
use App\Repository\LicensePlateRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;

class LicensePlateService
{
    /**
     * @var LicensePlateRepository
     */
    protected $licensePlateRepo;
    private EntityManagerInterface $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->licensePlateRepo = $em->getRepository(LicensePlate::class);
    }

    /**
     * @param string $licensePlate
     * @return string
     */
    public function normalizeLicensePlate(string $licensePlate): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $licensePlate));
    }

    /**
     * @param User $user
     * @return int
     */
    public function countLicensePlates(User $user): int
    {
        return count($this->licensePlateRepo->findBy(['user' => $user]));
    }

    /**
     * @param User $user
     * @return string|null
     */
    public function getFirstLicensePlate(User $user): ?string
    {
        $firstLicensePlate = $this->licensePlateRepo->findOneBy(['user' => $user]);
        if($firstLicensePlate)
            return $firstLicensePlate->getLicensePlate();
        return null;
    }

    /**
     * @param User $user
     * @return array|null
     */
    public function getAllLicensePlates(User $user): ?array
    {
        $allLicensePlates = $this->licensePlateRepo->findBy(['user' => $user]);
        foreach ($allLicensePlates as &$licensePlates)
        {
            $licensePlates = $licensePlates->getLicensePlate();
        }
        return $allLicensePlates;
    }

    /**
     * @param LicensePlate $licensePlate
     * @return float
     */

    public function getDurationBetweenUpdates(LicensePlate $licensePlate): float
    {
        return abs(strtotime(date('Y-m-d H:i:s')) - strtotime($licensePlate->getUpdatedAt()));
    }

    /**
     * @param User $user
     */
    public function  removeUser(User $user)
    {
        $cars = $this->licensePlateRepo->findBy(['user' => $user]);
        foreach ($cars as &$car)
        {
            $car->setUser(null);
            $this->em->flush();
        }
    }
}