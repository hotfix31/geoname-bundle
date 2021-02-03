<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\Country;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Repository\CountryRepository;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Reader;
use League\Csv\TabularDataReader;

class CountryImport extends ImportAbstract
{
    protected ?CountryRepository $repository = null;
    protected array $neighbours = [];

    protected function getCsvReader(File $file): TabularDataReader
    {
        $csv = parent::getCsvReader($file);

        if ($csv instanceof Reader) {
            $csv->setHeaderOffset(null);
        }

        return $csv;
    }

    public function import(File $file, ?callable $progress = null): void
    {
        parent::import($file, $progress);

        $this->processNeighbours();
        $this->updateGeoNameTable();
    }

    public function supports(string $support): bool
    {
        return 'countries' === $support;
    }

    /**
     * @return Country
     */
    protected function processRow(array $row): ?object
    {
        // skip comment lines
        if ('#' === $row[0][0]) {
            return null;
        }

        [
            $iso,
            $iso3,
            $isoNumeric,
            $fips,
            $name,
            $capital,
            $area,
            $population,
            $continent,
            $tld,
            $currency,
            $currencyName,
            $phone,
            $postalFormat,
            $postalRegex,
            $languages,
            $geoNameId,
            $neighbours,
        ] = $row;

        if (!\is_numeric($geoNameId)) {
            return null;
        }

        $object = $this->findByIsoOrCreateNew($iso);
        $object
            ->setIso($iso)
            ->setIso3($iso3)
            ->setIsoNumeric($isoNumeric)
            ->setFips($fips ?: null)
            ->setName($name ?: null)
            ->setCapital($capital ?: null)
            ->setArea($area ?: 0)
            ->setPopulation($population ?: 0)
            ->setContinent($continent)
            ->setTld($tld ?: null)
            ->setCurrency($currency ?: null)
            ->setCurrencyName($currencyName ?: null);

        $phone = \explode(' and ', $phone ?: '');
        $phone = \reset($phone);
        $phone = \preg_replace('/\D/', '', $phone);
        $object->setPhonePrefix($phone ?: null);

        $object->setPostalFormat($postalFormat ?: null);
        $object->setPostalRegex($postalRegex ?: null);
        $object->setLanguages(\explode(',', $languages) ?: null);

        /** @var GeoName $geoName */
        $geoName = $this->em->getReference(GeoName::class, $geoNameId);
        $object->setGeoName($geoName);

        if (!empty($neighbours)) {
            $this->neighbours[$iso] = \explode(',', $neighbours);
        }

        return $object;
    }

    protected function processNeighbours(): void
    {
        $table = $this->databaseImporterTools->getTableName(Country::class);
        $neighboursMapping = $this->em->getClassMetaData(Country::class)->associationMappings['neighbours'];

        $query = \sprintf(
            'REPLACE INTO %1$s (%2$s, %3$s) VALUES (
                     (SELECT id FROM %4$s _c1 WHERE _c1.iso = ? LIMIT 1),
                     (SELECT id FROM %4$s _c2 WHERE _c2.iso = ? LIMIT 1)
                    )',
            $neighboursMapping['joinTable']['name'],
            $neighboursMapping['joinTableColumns'][0],
            $neighboursMapping['joinTableColumns'][1],
            $table
        );

        foreach ($this->neighbours as $country => $neighbours) {
            foreach ($neighbours as $neighbour) {
                $this->em->getConnection()->executeStatement($query, [$country, $neighbour]);
            }
        }
    }

    protected function findByIsoOrCreateNew(string $iso): Country
    {
        return $this->getRepository()->findOneByIso($iso) ?? new Country();
    }

    protected function getRepository(): CountryRepository
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository(Country::class);
        }

        return $this->repository;
    }

    private function updateGeoNameTable(): void
    {
        $geoNameTableName = $this->databaseImporterTools->getTableName(GeoName::class);
        $countryTableName = $this->databaseImporterTools->getTableName(Country::class);

        $sql = <<<UpdateSelect
                        UPDATE {$geoNameTableName} SET country_id = (
                            SELECT id FROM {$countryTableName} _c WHERE _c.iso = {$geoNameTableName}.country_code LIMIT 1
                        )
            UpdateSelect;

        $this->em
            ->getConnection()
            ->executeStatement($sql);
    }
}
