<?php

namespace App\Repository;

use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\User;

/**
 * @method Tournament|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tournament|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tournament[]    findAll()
 * @method Tournament[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    // /**
    //  * @return Tournament[] Returns an array of Tournament objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tournament
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // All played Tournaments that are not hidden ordered by creationDate
    /**
     * @return Tournament[] Returns an array of Tournament objects
     */
    public function findAllByHidden($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.hidden = :val')
            ->setParameter('val', $value)
            ->orderBy('t.creationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // All played Tournaments that are not hidden ordered by creationDate (FINISHED)
    /**
     * @return Tournament[] Returns an array of Tournament objects
     */
    public function findAllByPlayerFinished(User $u)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.hidden = false')
            ->innerJoin('t.players','p')
            ->andWhere('p = :u')
            ->andWhere('t.finished = true')
            ->setParameter('u', $u)
            ->orderBy('t.creationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // All played Tournaments that are not hidden ordered by creationDate (PENDING) ***SIN HACER
    /**
     * @return Tournament[] Returns an array of Tournament objects
     */
    public function findAllByPlayerPending(User $u, \Datetime $d)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.hidden = false')
            ->innerJoin('t.players','p')
            ->andWhere('p = :u')
            ->andWhere('t.startDate < :d')
            ->andWhere('t.finished = false')
            ->setParameter('u', $u)
            ->setParameter('d', $d)
            ->orderBy('t.creationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // All played Tournaments that are not hidden ordered by creationDate (INPROGRESS) ***SIN HACER
    /**
     * @return Tournament[] Returns an array of Tournament objects
     */
    public function findAllByPlayerInProgress(User $u, \Datetime $d)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.hidden = false')
            ->innerJoin('t.players','p')
            ->andWhere('p = :u')
            ->andWhere('t.startDate > :d')
            ->andWhere('t.finished = false')
            ->setParameter('u', $u)
            ->setParameter('d', $d)
            ->orderBy('t.creationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // All created Tournaments that are not hidden ordered by creationDate ***SIN HACER
    /**
     * @return Tournament[] Returns an array of Tournament objects
     */
    public function findAllByCreator(User $u)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.hidden = false')
            ->andWhere('t.creatorUser = :u')
            ->setParameter('u', $u)
            ->orderBy('t.creationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }



}
