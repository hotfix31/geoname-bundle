<?php

namespace Hotfix\Bundle\GeoNameBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Hotfix\Bundle\GeoNameBundle\Entity\Feature;

/**
 * @method Feature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feature[]    findAll()
 * @method Feature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feature::class);
    }

    public function findOneByFeatureCode(string $class, string $code): ?Feature
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.class = :class')
            ->andWhere('f.code = :code')
            ->setParameter('code', $code)
            ->setParameter('class', $class)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
