<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Service\DatabaseImporter;

/**
 * @property DatabaseImporter  $databaseImporter
 * @property EntityManagerInterface $em
 */
trait GeoNameExistsTrait
{
    private array $geoNameExisting = [];

    public function geoNameExists(int $id): bool
    {
        if (!isset($this->geoNameExisting[$id])) {
            $table = $this->databaseImporter->getTableName(GeoName::class);

            $stmt = $this->em->getConnection()->prepare('SELECT 1 FROM ' . $table . ' WHERE id = ?');
            $stmt->execute([$id]);

            $this->geoNameExisting[$id] = (bool) $stmt->rowCount();
        }

        return $this->geoNameExisting[$id];
    }
}
