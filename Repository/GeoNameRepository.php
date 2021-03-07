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

    public function findByLocation(
        string $latitude,
        string $longitude,
        int $distanceMaximun = 50,
        int $populationMinimun = 2000,
        int $limit = 5
    ): array {
        $query = $this->createQueryBuilder('g')
            ->addSelect('f', 'c', 'a1', 'a2', 'a3', 'a4', 't')
            ->addSelect(
                '(DEGREES(
                ACOS(
                  SIN(RADIANS(:latitude)) * SIN(RADIANS(g.latitude)) + 
                  COS(RADIANS(:latitude)) * COS(RADIANS(g.latitude)) * 
                  COS(RADIANS(:longitude - g.longitude))
                ) 
              ) * 60 * 1.1515 * 1.609344) AS distance'
            )
            ->setParameter(':latitude', $latitude)
            ->setParameter(':longitude', $longitude)
            ->leftJoin('g.feature', 'f')
            ->leftJoin('g.country', 'c')
            ->leftJoin('g.admin1', 'a1')
            ->leftJoin('g.admin2', 'a2')
            ->leftJoin('g.admin3', 'a3')
            ->leftJoin('g.admin4', 'a4')
            ->leftJoin('g.timezone', 't');

        if ($distanceMaximun > 0) {
            $query
                ->andHaving('distance <= :distance')
                ->setParameter(':distance', $distanceMaximun);
        }

        if ($populationMinimun > 0) {
            $query
                ->andWhere('g.population >= :population')
                ->setParameter(':population', $populationMinimun);
        }

        return $query
            ->andWhere('g.latitude IS NOT NULL')
            ->andWhere('g.longitude IS NOT NULL')
            ->orderBy('distance', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
