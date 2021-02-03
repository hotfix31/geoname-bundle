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
    use GeoNameExistsTrait;

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
        $parentId = (int) $row['parentId'];
        $childId = (int) $row['childId'];

        if (!$this->geoNameExists($parentId) || !$this->geoNameExists($childId)) {
            return null;
        }

        $hierarchy = new Hierarchy();
        $hierarchy->setParent($this->em->getReference(GeoName::class, $parentId));
        $hierarchy->setChild($this->em->getReference(GeoName::class, $childId));
        $hierarchy->setType($row['type']);

        return $hierarchy;
    }

    public function supports(string $support): bool
    {
        return 'hierarchy' === $support;
    }
}
