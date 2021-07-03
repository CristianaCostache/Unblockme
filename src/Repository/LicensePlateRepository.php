<?php

namespace App\Repository;

use App\Entity\LicensePlate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LicensePlate|null find($id, $lockMode = null, $lockVersion = null)
 * @method LicensePlate|null findOneBy(array $criteria, array $orderBy = null)
 * @method LicensePlate[]    findAll()
 * @method LicensePlate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicensePlateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicensePlate::class);
    }

    // /**
    //  * @return LicensePlate[] Returns an array of LicensePlate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LicensePlate
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
