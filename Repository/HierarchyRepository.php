<?php

namespace Hotfix\Bundle\GeoNameBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Hotfix\Bundle\GeoNameBundle\Entity\Hierarchy;

/**
 * @method Hierarchy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hierarchy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hierarchy[]    findAll()
 * @method Hierarchy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HierarchyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hierarchy::class);
    }
}
