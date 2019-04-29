<?php

namespace App\Repository;

use App\Entity\Flat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Flat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Flat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Flat[]    findAll()
 * @method Flat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Flat::class);
    }
}
