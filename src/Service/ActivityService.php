<?php


namespace App\Service;

use App\Entity\Activity;
use App\Entity\User;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ActivityService
{
    /**
     * @var ActivityRepository
     */
    protected $activityRepo;
    private EntityManagerInterface $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->activityRepo = $em->getRepository(Activity::class);
    }

    /**
     * @param string $licensePlate
     * @return array|null
     */
    public function iveBlockedSomebody(string $licensePlate): ?array
    {
        $blockees = $this->activityRepo->findByBlocker($licensePlate);
        if(count($blockees) == 0)
        {
            return null;
        }
        return $blockees;
    }

    /**
     * @param string $licensePlate
     * @return array|null
     */
    public function whoBlockedMe(string $licensePlate): ?array
    {
        $blockers = $this->activityRepo->findByBlockee($licensePlate);
        if(count($blockers) == 0)
        {
            return null;
        }
        return $blockers;
    }

    /**
     * @param User $user
     * @param LicensePlateService $licensePlateService
     * @return array|null
     */
    public function allMyBlockees(User $user, LicensePlateService $licensePlateService): ?array
    {
        $allLicensePlates = $licensePlateService->getAllLicensePlates($user);
        $blockees = $this->activityRepo->findBy(['blocker' => $allLicensePlates, 'status' => [0, 1, 2]]);
        return $blockees;
    }

    /**
     * @param User $user
     * @param LicensePlateService $licensePlateService
     * @return array|null
     */
    public function allMyBlockers(User $user, LicensePlateService $licensePlateService): ?array
    {
        $allLicensePlates = $licensePlateService->getAllLicensePlates($user);
        $blockers = $this->activityRepo->findBy(['blockee' => $allLicensePlates, 'status' => [0, 1, 2]]);
        return $blockers;
    }

    /**
     * @param Activity $activity
     * @return float
     */
    public function getDurationBetweenUpdates(Activity $activity): float
    {
        return abs(strtotime(date('Y-m-d H:i:s')) - strtotime($activity->getCreatedAt()));
    }

    public function checkActivities()
    {
        $allActivities = $this->activityRepo->findAll();
        //dd($allActivities);
        foreach ($allActivities as &$activity)
        {
            $diff = $this->getDurationBetweenUpdates($activity);
            if($diff > 10)
            {
                $activity->setStatus(2);
            }
        }
        $this->em->flush();
    }
}