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
            ->addSelect('COUNT(m.id) AS HIDDEN msgcount')
            ->leftJoin('p.messages', 'm')
            ->groupBy('m.post')
            ->where('p.subcategory = :sub')
            ->andWhere('p.karma > 0')
            ->andWhere('p.createdAt > :date')
            ->setParameter('sub', $subcategory)
            ->setParameter('date', $date)
            ->orderBy('p.karma', 'DESC')
            ->addOrderBy('msgcount', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function findByTop(SubCategory $subcategory)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('COUNT(m.id) AS HIDDEN msgcount')
            ->leftJoin('p.messages', 'm')
            ->groupBy('m.post')
            ->where('p.subcategory = :sub')
            ->andWhere('p.karma > 0')
            ->setParameter('sub', $subcategory)
            ->orderBy('p.karma', 'DESC')
            ->addOrderBy('msgcount', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function search(string $query)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->where('p.title LIKE :query')
            ->orWhere('a.username LIKE :query')
            ->orWhere('p.slug LIKE :query')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
}
