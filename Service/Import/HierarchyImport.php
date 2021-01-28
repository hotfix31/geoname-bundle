<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Entity\Hierarchy;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

class HierarchyImport extends ImportAbstract
{
    protected function getCsvReader(File $file): TabularDataReader
    {
        $this->databaseImporterTools->truncate(Hierarchy::class);

        $file2 = $file->unzip();
        $csv = parent::getCsvReader($file2);
        if ($csv instanceof Reader) {
            $csv->setHeaderOffset(null);
        }

        return Statement::create()
            ->process($csv, ['parentId', 'childId', 'type']);
    }

    /**
     * @return Hierarchy|null
     */
    protected function processRow(array $row): ?object
    {
        $hierarchy = new Hierarchy();
        $hierarchy->setParent($this->em->getReference(GeoName::class, $row['parentId']));
        $hierarchy->setChild($this->em->getReference(GeoName::class, $row['childId']));

        return $hierarchy;
    }

    public function supports(string $support): bool
    {
        return $support === 'hierarchy';
    }
}
