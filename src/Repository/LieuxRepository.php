<?php

namespace App\Repository;

use App\Entity\Lieux;
use App\Entity\TypeLieux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lieux>
 *
 * @method Lieux|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lieux|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lieux[]    findAll()
 * @method Lieux[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LieuxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieux::class);
    }

    public function save(Lieux $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Lieux $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function myFindAllWithPaging(string $libelle, $currentPage): Paginator
    {
        $query = $this->createQueryBuilder('l')
            ->join('l.TypeLieux', 'tl')
            ->addSelect('tl')
            ->where('tl.id = l.TypeLieux')
            ->where('tl.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->orderBy('l.id','DESC')
            ->getQuery()
            ->setFirstResult(($currentPage - 1) * 20) // Premier élément de la page
            ->setMaxResults(20); // Nombre d'éléments par page
        return new Paginator($query);
    }

    public function findWithKeyWord(string $libelle, Lieux $criteria )
    {
        $args = array(explode(' ',$criteria->getNom()));
        $query = $this->createQueryBuilder('l')
            ->join('l.TypeLieux', 'tl')
            ->addSelect('tl')
            ->where('tl.id = l.TypeLieux')
            ->where('tl.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->orderBy('l.id', 'DESC');
        for ($i=0; $i<=count($args) - 1; $i++) {
            $query->andWhere('l.nom LIKE :searchTerm')
                ->setParameter('searchTerm', "%{$args[0][$i]}%");
        }
        return $query->getQuery()
            ->getResult();
    }

//    /**
//     * @return Lieux[] Returns an array of Lieux objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Lieux
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
