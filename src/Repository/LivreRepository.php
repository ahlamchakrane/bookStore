<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Livre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livre[]    findAll()
 * @method Livre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    // /**
    //  * @return Livre[] Returns an array of Livre objects
    //  */

    public function sortByTitre()
    {
        return $this->createQueryBuilder('SELECT * FROM livre l ')
            // ->Where('l.exampleField = :val')
            //  ->setParameter('val', $value)
            ->orderBy('l.titre', 'ASC')
            // ->setMaxResults(10)
            //->getQuery()
            //->getResult()
        ;
    }
    // public function findByAuteur($nom)
    // {
    //     return $this->createQueryBuilder('SELECT livres FROM auteur l  ')
    //         ->andWhere('l.nom_prenom like :val')
    //         ->setParameter('val', "%" . $nom . "%")
    //         ->orderBy('l.nom_prenom', 'ASC')
    //         // ->setMaxResults(10)
    //         //->getQuery()
    //         //->getResult()
    //     ;
    // }


    /*
    public function findOneBySomeField($value): ?Livre
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
