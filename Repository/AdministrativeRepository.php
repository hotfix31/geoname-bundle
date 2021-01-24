<?php

namespace Hotfix\Bundle\GeoNameBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;

/**
 * @method Administrative|null find($id, $lockMode = null, $lockVersion = null)
 * @method Administrative|null findOneBy(array $criteria, array $orderBy = null)
 * @method Administrative|null findOneByCode(string $code, array $orderBy = null)
 * @method Administrative[]    findAll()
 * @method Administrative[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdministrativeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Administrative::class);
    }
}
