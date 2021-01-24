<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;
use Hotfix\Bundle\GeoNameBundle\Entity\Country;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Entity\Timezone;
use Hotfix\Bundle\GeoNameBundle\Repository\GeoNameRepository;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

class GeoNameImport extends ImportAbstract
{
    protected ?\SplFileObject $originalFile = null;
    protected ?GeoNameRepository $repository = null;
    protected array $adminReference = [];
    protected array $countryReference = [];
    protected array $timezoneReference = [];
    protected int $flushModulo = 100;

    public function getCsvReader(\SplFileObject $file): TabularDataReader
    {
        $zip = new \ZipArchive();
        $zip->open($file->getRealPath());
        $content = $zip->getFromName($file->getBasename('.zip').'.txt');

        $temp = new \SplTempFileObject();
        $temp->fwrite($content);

        $csv = Reader::createFromFileObject($temp);
        $csv->setDelimiter("\t");
        $csv->setHeaderOffset(null);
        $csv->skipEmptyRecords();

        return Statement::create()
            ->process($csv,
                [
                    'geoNameId',
                    'name',
                    'asciiName',
                    'alternatenames',
                    'latitude',
                    'longitude',
                    'featureClass',
                    'featureCode',
                    'countryCode',
                    'cc2',
                    'admin1Code',
                    'admin2Code',
                    'admin3Code',
                    'admin4Code',
                    'population',
                    'elevation',
                    'dem',
                    'timezone',
                    'modificationDate',
                ]
            );
    }

    public function supports(string $support): bool
    {
        return $support === 'geonames';
    }

    public function processRow(array $row): ?object
    {
        [
            $geoNameId,
            $name,
            $asciiName,
            $alternateNames,
            $latitude,
            $longitude,
            $featureClass,
            $featureCode,
            $countryCode,
            $cc2,
            $admin1Code,
            $admin2Code,
            $admin3Code,
            $admin4Code,
            $population,
            $elevation,
            $dem,
            $timezone,
            $modificationDate,
        ] = array_values($row);

        if (!preg_match('/^\d{4}\-\d{2}-\d{2}$/', $modificationDate)) {
            return null;
        }

        $object = $this->findByIdOrCreateNew($geoNameId);
        $object->setId($geoNameId);
        $object->setName($name);
        $object->setAsciiName($asciiName);
        $object->setLatitude($latitude);
        $object->setLongitude($longitude);
        $object->setFeatureClass($featureClass);
        $object->setFeatureCode($featureCode);
        $object->setPopulation((is_numeric($population)) ? (int)$population : null);
        $object->setElevation((is_numeric($elevation)) ? (int)$elevation : null);
        $object->setDem((is_numeric($dem)) ? (int)$dem : null);
        $object->setCc2($cc2);
        $object->setCountryCode($countryCode);

        if (!$object->getCountry() || $object->getCountry()->getIso() !== $countryCode) {
            $object->setCountry($this->getReferenceByCountryCode($countryCode));
        }

        foreach (range(1, 4) as $number) {
            if (!$object->{"getAdmin${number}"}() || $object->{"getAdmin${number}"}()->getCode(
                ) !== ${"admin${number}Code"}) {
                $object->{"setAdmin${number}"}($this->getReferenceByAdminCode(${"admin${number}Code"}));
            }
        }

        if (!$object->getTimezone() || $object->getTimezone()->getTimezone() !== $timezone) {
            $object->setTimezone($this->getReferenceByTimezone($timezone));
        }

        return $object;
    }

    protected function findByIdOrCreateNew(int $geoNameId): GeoName
    {
        return $this->getRepository()->find($geoNameId) ?? new GeoName();
    }

    protected function getRepository(): GeoNameRepository
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository(GeoName::class);
        }

        return $this->repository;
    }

    public function getReferenceByCountryCode(string $countryCode): ?Country
    {
        if (!isset($this->countryReference[$countryCode])) {
            $table = $this->getTableName(Country::class);
            $id = $this->em->getConnection()->executeStatement(
                'SELECT id FROM '.$table.' WHERE iso = :code',
                ['code' => $countryCode]
            );

            $this->countryReference[$countryCode] = $id;
        }

        return $this->countryReference[$countryCode] ? $this->em->getReference(
            Country::class,
            $this->countryReference[$countryCode]
        ) : null;
    }

    public function getReferenceByAdminCode(string $adminCode): ?Administrative
    {
        if (!isset($this->adminReference[$adminCode])) {
            $table = $this->getTableName(Administrative::class);
            $id = $this->em->getConnection()->executeStatement(
                'SELECT id FROM '.$table.' WHERE code = :code',
                ['code' => $adminCode]
            );

            $this->adminReference[$adminCode] = $id;
        }

        return $this->adminReference[$adminCode] ? $this->em->getReference(
            Administrative::class,
            $this->adminReference[$adminCode]
        ) : null;
    }

    public function getReferenceByTimezone(string $timezone): ?Timezone
    {
        if (!isset($this->timezoneReference[$timezone])) {
            $table = $this->getTableName(Timezone::class);
            $id = $this->em->getConnection()->executeStatement(
                'SELECT id FROM '.$table.' WHERE timezone = :timezone',
                ['timezone' => $timezone]
            );

            $this->timezoneReference[$timezone] = $id;
        }

        return $this->timezoneReference[$timezone] ? $this->em->getReference(
            Timezone::class,
            $this->timezoneReference[$timezone]
        ) : null;
    }
}
