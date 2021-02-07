<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Service\DatabaseImporter;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\TabularDataReader;

abstract class ImportAbstract implements ImportInterface
{
    protected EntityManagerInterface $em;
    protected DatabaseImporter $databaseImporter;
    protected int $flushModulo = 1000;

    public function __construct(EntityManagerInterface $em, DatabaseImporter $databaseImporter)
    {
        $this->em = $em;
        $this->databaseImporter = $databaseImporter;
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
        $max = \count($csv);

        if ($this->flushModulo >= $max) {
            $this->flushModulo = \round($max / 3);
        }

        $this->databaseImporter->disabledLogger();

        $pos = 0;
        $this->em->beginTransaction();

        foreach ($csv as $row) {
            $row = \array_map('trim', $row);
            $object = $this->processRow($row);

            if (!$object) {
                continue;
            }

            $this->em->persist($object);
            \is_callable($progress) && $progress(($pos++) / $max);

            if ($pos % $this->flushModulo) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->commit();
        $this->em->clear();

        $this->databaseImporter->restoreLogger();
    }

    abstract protected function processRow(array $row): ?object;
}
