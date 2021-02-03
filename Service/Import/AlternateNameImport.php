<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\AlternateName;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

class AlternateNameImport extends ImportAbstract
{
    use GeoNameExistsTrait;

    public function supports(string $support): bool
    {
        return 'alternate-names' === $support;
    }

    protected function getCsvReader(File $file): TabularDataReader
    {
        $this->databaseImporterTools->truncate(AlternateName::class);

        $file2 = $file->unzip();
        $csv = parent::getCsvReader($file2);

        if ($csv instanceof Reader) {
            $csv->setHeaderOffset(null);
        }

        return Statement::create()
            ->process($csv,
                [
                    'alternateNameId',
                    'geoNameId',
                    'isoLanguage',
                    'name',
                    'isPreferredName',
                    'isShortName',
                    'isColloquial',
                    'isHistoric',
                    'yearFrom',
                    'yearTo',
                ]
            );
    }

    /**
     * @return AlternateName|null
     */
    protected function processRow(array $row): ?object
    {
        $geoNameId = (int) $row['geoNameId'];

        if (!$this->geoNameExists($geoNameId)) {
            return null;
        }

        /** @var GeoName $geoName */
        $geoName = $this->em->getReference(GeoName::class, $geoNameId);

        return (new AlternateName())
            ->setGeoName($geoName)
            ->setId($row['alternateNameId'])
            ->setName($row['name'])
            ->setIsoLanguage($row['isoLanguage'] ?? null)
            ->setIsPreferredName((bool) $row['isPreferredName'])
            ->setIsShortName((bool) $row['isShortName'])
            ->setIsColloquial((bool) $row['isColloquial'])
            ->setIsHistoric((bool) $row['isHistoric'])
            ->setYearFrom($row['yearFrom'] ?? null)
            ->setYearTo($row['yearTo'] ?? null);
    }
}
