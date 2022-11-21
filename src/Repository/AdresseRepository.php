<?php

namespace App\Repository;

use App\Entity\Adresse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Adresse>
 *
 * @method Adresse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adresse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adresse[]    findAll()
 * @method Adresse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdresseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adresse::class);
    }
    public function save(Adresse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Adresse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllWithParameter($codePost, $ville, $rue, $nbRue, $nbAppart): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.codePostale = :codePos')
            ->setParameter('codePos', $codePost)
            ->andWhere('a.ville = :ville')
            ->setParameter('ville', $ville)
            ->andWhere('a.rue = :rue')
            ->setParameter('rue', $rue)
            ->andWhere('a.numeroRue = :nbRue')
            ->setParameter('nbRue', $nbRue)
            ->andWhere('a.numeroAppart = :nbAppart')
            ->setParameter('nbAppart',$nbAppart)
            ->getQuery()
            ->getResult()
        ;
    }

//    public function findOneBySomeField($value): ?Adresse
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
