<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;
use Hotfix\Bundle\GeoNameBundle\Entity\Timezone;
use Hotfix\Bundle\GeoNameBundle\Repository\TimezoneRepository;

class TimeZoneImport extends ImportAbstract
{
    protected ?TimezoneRepository $repository = null;

    protected function getRepository(): TimezoneRepository
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository(Timezone::class);
        }

        return $this->repository;
    }

    protected function findByTimezoneOrCreateNew(string $code): Timezone
    {
        return $this->getRepository()->findOneByTimezone($code) ?? new Timezone();
    }

    public function supports(string $support): bool
    {
        return $support === 'timezones';
    }

    public function processRow(array $row): ?object
    {
        [$countryCode, $timezone, $gmtOffset, $dstOffset, $rawOffset] = array_values($row);

        $object = $this->findByTimezoneOrCreateNew($timezone);
        $object->setTimezone($timezone);
        $object->setCountryCode($countryCode);
        $object->setGmtOffset((float) $gmtOffset);
        $object->setDstOffset((float) $dstOffset);
        $object->setRawOffset((float) $rawOffset);

        return $object;
    }
}
