<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\AppointmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointment[]    findAll()
 * @method Appointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{

    private $activeStatusName = 'created';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findAllActive() {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->innerJoin('a.status', 's', 'WITH', 's.id = a.status')
            ->where('s.name = :statusName')
            ->setParameter('statusName', $this->activeStatusName)
            ->getQuery()
            ->execute();
    }

    public function findByDateRange($startDate, $endDate)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.status', 's', 'WITH', 's.id = a.status')
            ->Where('a.start_date <= :startDate AND a.end_date > :startDate')
            ->orWhere('a.start_date < :endDate AND a.end_date >= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
            ->andwhere('s.name = :statusName')
                ->setParameter('statusName', $this->activeStatusName)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('a')
            ->where('a.something = :value')->setParameter('value', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

}
