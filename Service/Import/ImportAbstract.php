<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Service\DatabaseImporterTools;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\TabularDataReader;

abstract class ImportAbstract implements ImportInterface
{
    protected EntityManagerInterface $em;
    protected DatabaseImporterTools $databaseImporterTools;
    protected int $flushModulo = 1000;

    public function __construct(EntityManagerInterface $em, DatabaseImporterTools $databaseImporterTools)
    {
        $this->em = $em;
        $this->databaseImporterTools = $databaseImporterTools;
    }

    protected function getCsvReader(File $file): TabularDataReader
    {
        $csv = $file->getCsvReader();
        $csv->setDelimiter("\t");
        $csv->setHeaderOffset(0);
        $csv->skipEmptyRecords();

        return $csv;
    }

    public function import(File $file, ?callable $progress = null): void
    {
        $csv = $this->getCsvReader($file);
        $max = count($csv);
        if ($this->flushModulo >= $max) {
            $this->flushModulo = round($max/3);
        }

        $this->databaseImporterTools->disabledLogger();

        $pos = 0;
        $this->em->beginTransaction();
        foreach ($csv as $row) {
            $row = array_map('trim', $row);
            $object = $this->processRow($row);
            if (!$object) {
                continue;
            }

            !$object->getId() && $this->em->persist($object);
            if ($pos % $this->flushModulo) {
                $this->em->flush();
                $this->em->clear();

                is_callable($progress) && $progress(($pos++) / $max);
            }
        }

        $this->em->flush();
        $this->em->commit();
        $this->em->clear();

        $this->databaseImporterTools->restoreLogger();
    }

    abstract protected function processRow(array $row): ?object;
}