<?php

namespace Hotfix\Bundle\GeoNameBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;

/**
 * @method GeoName|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeoName|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeoName[]    findAll()
 * @method GeoName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeoNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeoName::class);
    }
}
