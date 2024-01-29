<?php

namespace App\Repository;

use App\Entity\EmailEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailEvent>
 *
 * @method EmailEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailEvent[]    findAll()
 * @method EmailEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailEvent::class);
    }

//    /**
//     * @return EmailEvent[] Returns an array of EmailEvent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmailEvent
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
