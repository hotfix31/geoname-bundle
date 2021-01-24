<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\Country;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Repository\CountryRepository;

class CountryImport extends ImportAbstract
{
    protected ?CountryRepository $repository = null;

    public function import(\SplFileObject $file, ?callable $progress = null): void
    {
        parent::import($file, $progress);

        $geoNameTableName = $this->getTableName(GeoName::class);
        $countryTableName = $this->getTableName(Country::class);

        $sql = <<<UpdateSelect
            UPDATE {$geoNameTableName} SET
                country_id = (
                    SELECT 
                        id
                    FROM
                        {$countryTableName} _c
                    WHERE
                       _c.iso = {$geoNameTableName}.country_code
                    LIMIT 1
                )
UpdateSelect;

        $this->em
            ->getConnection()
            ->executeStatement($sql);
    }

    public function supports(string $support): bool
    {
        return $support === 'countries';
    }

    /**
     * @return Country
     */
    public function processRow(array $row): ?object
    {
        if (count($row) < 17) {
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
        if (!is_numeric($geoNameId)) {
            return null;
        }

        $object = $this->findByIdOrCreateNew($geoNameId);
        $object->setId($geoNameId);
        $object->setIso($iso);
        $object->setIso3($iso3);
        $object->setIsoNumeric($isoNumeric);
        $object->setFips($fips ?: null);
        $object->setName($name ?: null);
        $object->setCapital($capital ?: null);
        $object->setArea($area ?: 0);
        $object->setPopulation($population ?: 0);
        $object->setTld($tld ?: null);
        $object->setCurrency($currency ?: null);
        $object->setCurrencyName($currencyName ?: null);

        $phone = explode(' and ', $phone ?: '');
        $phone = reset($phone);
        $phone = preg_replace('/\D/', '', $phone);
        $object->setPhonePrefix($phone ?: null);

        $object->setPostalFormat($postalFormat ?: null);
        $object->setPostalRegex($postalRegex ?: null);
        $object->setLanguages(explode(',', $languages) ?: null);
        $object->setGeoName($this->em->getReference(GeoName::class, $geoNameId));

        return $object;
    }

    protected function findByIdOrCreateNew(int $id): Country
    {
        return $this->getRepository()->find($id) ?? new Country();
    }

    protected function getRepository(): CountryRepository
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository(Country::class);
        }

        return $this->repository;
    }
}
