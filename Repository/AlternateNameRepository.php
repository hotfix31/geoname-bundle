<?php

namespace Hotfix\Bundle\GeoNameBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Hotfix\Bundle\GeoNameBundle\Entity\AlternateName;

/**
 * @method AlternateName|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlternateName|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlternateName[]    findAll()
 * @method AlternateName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlternateNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlternateName::class);
    }
}
