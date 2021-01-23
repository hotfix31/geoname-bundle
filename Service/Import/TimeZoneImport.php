<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\Timezone;
use GuzzleHttp\Promise\Promise;
use SplFileObject;

class TimeZoneImport extends ImportAbstract
{
    /*public function import(\SplFileObject $file, callable $progress = null)
    {
        $self = $this;
        /** @var Promise $promise *
        $promise = (new Promise(function () use ($file, $progress, $self, &$promise) {
            $promise->resolve(
                $self->_import($file, $progress)
            );
        }));

        return $promise;
    }*/

    public function import(\SplFileObject $file, ?callable $progress = null)
    {
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl("\t");
        $file->seek(PHP_INT_MAX);

        $max = $file->key();
        $file->seek(1); //skip header

        $timezoneRepository = $this->em->getRepository("HotfixGeoNameBundle:Timezone");

        $pos = -1;

        foreach ($file as $row) {
            if($pos == -1){
                $pos++;
                continue;
            }
            $row = array_map('trim',$row);
            list(
                $countryCode,
                $timezone,
                $gmtOffset,
                $dstOffset,
                $rawOffset
                ) = $row;


            $object = $timezoneRepository->findOneBy(['timezone' => $timezone]) ?: new Timezone();
            $object->setTimezone($timezone);
            $object->setCountryCode($countryCode);
            $object->setGmtOffset((float) $gmtOffset);
            $object->setDstOffset((float) $dstOffset);
            $object->setRawOffset((float) $rawOffset);

            !$object->getId() && $this->em->persist($object);
            is_callable($progress) && $progress(($pos++) / $max);
        }

        $this->em->flush();
        $this->em->clear();

        return true;
    }

    public function supports(string $support): bool
    {
        return $support === 'timezones';
    }
}
