<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\TabularDataReader;

abstract class ImportAbstract implements ImportInterface
{
    protected EntityManagerInterface $em;
    protected int $flushModulo = 1000;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getTableName(string $class): string
    {
        return $this->em
            ->getClassMetadata($class)
            ->getTableName();
    }

    protected function getCsvReader(\SplFileObject $file): TabularDataReader
    {
        $csv = Reader::createFromFileObject($file);
        $csv->setDelimiter("\t");
        $csv->setHeaderOffset(0);
        $csv->skipEmptyRecords();

        return $csv;
    }

    public function import(\SplFileObject $file, ?callable $progress = null): void
    {
        $file->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $csv = $this->getCsvReader($file);
        $max = count($csv);

        $pos = 0;
        $this->em->beginTransaction();
        foreach ($csv as $row) {
            $row = array_map('trim', $row);
            $object = $this->processRow($row);
            if (!$object) {
                continue;
            }

            !$object->getId() && $this->em->persist($object);
            is_callable($progress) && $progress(($pos++) / $max);

            if ($pos % $this->flushModulo) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->commit();
        $this->em->clear();

        $file->isFile();
    }
}