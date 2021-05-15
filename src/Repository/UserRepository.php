<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Points;
use App\Entity\Province;
use App\Entity\VideoGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // Finds All Users That Are Verified
    /**
     * @return User[] Returns an array of User objects
     */
    public function findByVerfied($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.verified = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // Global Ranking
    /**
     * @return User[] Returns an array of User objects
     */
    public function globalRanking()
    {
        return $this->createQueryBuilder('u')
            ->select('u.email, u.name, u.surname, u.profilePicture, IDENTITY(u.province)')
            ->addSelect('SUM(p.amount) as totalAmount')
            ->andWhere('p.user = u')
            ->from('App\Entity\Points', 'p')
            ->orderBy('totalAmount', 'DESC')
            ->groupBy("u.id")
            ->getQuery()
            ->getResult()
        ;
    }

    // Ranking
    /**
     * @return User[] Returns an array of User objects
     */
    public function ranking(Province $province, VideoGame $videogame, $year)
    {
        return $this->createQueryBuilder('u')
            ->select('u.email, u.name, u.surname, u.profilePicture, IDENTITY(u.province)')
            ->addSelect('SUM(p.amount) as totalAmount')
            ->andWhere('p.user = u')
            ->andWhere('u.province = :province')
            ->andWhere('year(p.datetime) = :year')
            ->andWhere('p.tournament = t')
            ->andWhere('t.videogame = :videogame')
            ->setParameter('province', $province)
            ->setParameter('year', $year)
            ->setParameter('videogame', $videogame)
            ->from('App\Entity\Points', 'p')
            ->from('App\Entity\Tournament', 't')
            ->orderBy('totalAmount', 'DESC')
            ->groupBy("u.id")
            ->getQuery()
            ->getResult()
        ;
    }

    // // Ranking
    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    // public function ranking(Province $province, 
    // //VideoGame $videogame, 
    // $year)
    // {
    //     return $this->createQueryBuilder('u')
    //         ->select('u.email, u.name, u.surname, u.profilePicture, IDENTITY(u.province)')
    //         ->addSelect('SUM(p.amount) as totalAmount')
    //         //->addSelect('IDENTITY(p.tournament) as tournament, IDENTITY(tournament.videogame) as v')
    //         ->andWhere('p.user = u')
    //         ->andWhere('u.province = :province')
    //         //->andWhere('v = :videogame')
    //         ->andWhere('year(p.datetime) = :year')
    //         ->setParameter('province', $province)
    //         //->setParameter('videogame', $videogame)
    //         ->setParameter('year', $year)
    //         ->from('App\Entity\Points', 'p')
    //         ->orderBy('totalAmount', 'DESC')
    //         ->groupBy("u.id")
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }

    // // Global Ranking
    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    // public function globalRanking()
    // {
    //     return $this->createQueryBuilder('u')
    //         //->setParameter('val', $value)
    //         ->innerJoin('u.points','p')
    //         ->select('u, SUM(p.amount) as totalAmount')
    //         ->andWhere('p.user = u')
    //         ->orderBy('totalAmount', 'DESC')
    //         ->addGroupBy("1")
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }
}
