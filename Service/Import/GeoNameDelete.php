<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Service\DatabaseImporter;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

class GeoNameDelete implements ImportInterface
{
    use GeoNameExistsTrait;

    protected EntityManagerInterface $em;
    protected DatabaseImporter $databaseImporter;
    protected int $flushModulo = 1000;
    protected ?int $countLines = null;

    public function __construct(EntityManagerInterface $em, DatabaseImporter $databaseImporter)
    {
        $this->em = $em;
        $this->databaseImporter = $databaseImporter;
    }

    protected function getCsvReader(File $file): TabularDataReader
    {
        $csv = $file->getCsvReader();
        $csv->setDelimiter("\t");
        $csv->skipEmptyRecords();

        return Statement::create()
            ->process($csv, ['id', 'name', 'comment']);
    }

    public function supports(string $support): bool
    {
        return 'geonames-delete' === $support;
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
            if (!$this->geoNameExists($row['id'])) {
                continue;
            }

            $this->em->remove($this->em->find(GeoName::class, $row['id']));
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
}
