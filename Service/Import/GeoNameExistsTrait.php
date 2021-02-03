<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Service\DatabaseImporterTools;

/**
 * @property DatabaseImporterTools  $databaseImporterTools
 * @property EntityManagerInterface $em
 */
trait GeoNameExistsTrait
{
    private array $geoNameExisting = [];

    public function geoNameExists(int $id): bool
    {
        if (!isset($this->geoNameExisting[$id])) {
            $table = $this->databaseImporterTools->getTableName(GeoName::class);
            $this->geoNameExisting[$id] = (bool) $this->em->getConnection()->executeStatement(
                'SELECT 1 FROM ' . $table . ' WHERE id = ?',
                [$id]
            );
        }

        return $this->geoNameExisting[$id];
    }
}
