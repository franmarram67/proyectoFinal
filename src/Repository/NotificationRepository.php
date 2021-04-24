<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\User;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    // /**
    //  * @return Notification[] Returns an array of Notification objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    //Find All Notifications of User with id ordered by creationDate
    /**
     * @return Notification[] Returns an array of Notification objects
     */
    public function findAllOrderedByCreationDate(User $u)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :u')
            ->setParameter('u', $u)
            ->orderBy('n.creationDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //Find All Unseen Notifications of User
    // ARREGLAR LOS ERRORES QUE SALEN en esta consulta y quizá en la de arriba que no lo he comprobado.
    /**
     * @return Notification[] Returns an array of Notification objects
     */
    public function findAllUnseenOfUser(User $u)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :u and n.seen = false')
            ->setParameter('u', $u)
            ->getQuery()
            ->getResult()
        ;
    }
}
