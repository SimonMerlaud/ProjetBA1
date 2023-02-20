<?php

namespace App\Repository;

use App\Entity\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 *
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function save(Booking $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Booking $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Booking[]
     */
    public function findBetweenDates($start, $end, $magId=0)
    {
        $query = $this->createQueryBuilder('b')
            ->andWhere('b.beginAt < :end')
            ->andWhere('b.endAt > :start')
            ->andWhere('b.lieux is NULL')
            ->andWhere('b.MagasinId = :magasin_id')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
        ->setParameter('magasin_id', $magId);
        return $query->getQuery()->getResult();
    }

    public function findWithId($contactId,$bookingId)
    {
        $query = $this->createQueryBuilder('b')
            ->join('b.contacts','contacts')
            ->andWhere('contacts= :contact')
            ->andWhere('b.lieux is NULL')
            ->andWhere('b.id= :booking')
            ->setParameter('contact',$contactId)
            ->setParameter('booking', $bookingId);
        return $query->getQuery()->getResult();
    }

    public function findBookingBene($contactId){
        $query = $this->createQueryBuilder('b')
            ->join('b.contacts', 'contacts')
            ->andWhere('contacts.id= :contactId')
            ->andWhere('b.lieux is NULL')
            ->setParameter('contactId', $contactId);
        return $query->getQuery()->getResult();
    }
//    /**
//     * @return Booking[] Returns an array of Booking objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Booking
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
