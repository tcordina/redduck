<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findByHot(SubCategory $subcategory)
    {
        $date = (new \DateTime('now'))->modify('-24 hours');
        return $this->createQueryBuilder('p')
            ->addSelect('COUNT(u.id) AS HIDDEN uvcount')
            ->join('p.upvotes', 'u')
            ->groupBy('p.id')
            ->where('p.subcategory = :sub')
            ->andWhere('p.createdAt > :date')
            ->setParameter('sub', $subcategory)
            ->setParameter('date', $date)
            ->orderBy('uvcount', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    public function findByTop($subcategory)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('COUNT(u.id) AS HIDDEN uvcount')
            ->join('p.upvotes', 'u')
            ->groupBy('p.id')
            ->where('p.subcategory = :sub')
            ->setParameter('sub', $subcategory)
            ->orderBy('uvcount', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
