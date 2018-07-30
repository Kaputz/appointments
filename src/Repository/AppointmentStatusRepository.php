<?php

namespace App\Repository;

use App\Entity\AppointmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AppointmentStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppointmentStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppointmentStatus[]    findAll()
 * @method AppointmentStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AppointmentStatus::class);
    }

    public function findOneByName($value): ?AppointmentStatus
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

//    /**
//     * @return AppointmentStatus[] Returns an array of AppointmentStatus objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AppointmentStatus
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
