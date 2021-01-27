<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;
use Hotfix\Bundle\GeoNameBundle\Entity\Country;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Entity\Timezone;
use Hotfix\Bundle\GeoNameBundle\Service\DatabaseImporterTools;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;
use Symfony\Component\Stopwatch\Stopwatch;

class GeoNameImport implements ImportInterface
{
    private EntityManagerInterface $em;
    private DatabaseImporterTools $databaseImporterTools;
    private array $adminReference = [];
    private array $countryReference = [];
    private array $timezoneReference = [];
    private ?int $countLines = null;
    protected Stopwatch $stopwatch;

    public function __construct(EntityManagerInterface $em, DatabaseImporterTools $databaseImporterTools, Stopwatch $stopwatch)
    {
        $this->em = $em;
        $this->databaseImporterTools = $databaseImporterTools;
        $this->stopwatch = $stopwatch;
    }

    private function getNbRecords(): int
    {
        return $this->nbRecords ?? 0;
    }

    public function getCountLines(): ?int
    {
        return $this->countLines;
    }

    protected function setCountLines(?int $countLines): self
    {
        $this->countLines = $countLines;

        return $this;
    }

    public function import(File $file, ?callable $progress = null): void
    {
        $this->stopwatch->start('CSV Reader', 'import');
        $csv = $this->getCsvReader($file);
        $this->stopwatch->stop('CSV Reader');

        $pos = 0;
        $replaces = [];
        $max = $this->getNbRecords();
        $this->em->beginTransaction();

        $this->stopwatch->start('CSV parser', 'import');
        foreach ($csv as $row) {
            $row = array_map('trim', $row);
            unset($row['alternatenames']);

            $row['country_id'] = $this->getReferenceByCountryCode($row['country_code']);

            $row['timezone_id'] = $this->getReferenceByTimezone($row['timezone']);
            unset($row['timezone']);

            foreach (range(1, 4) as $number) {
                $keyCode = "admin${number}Code";
                $keyId = "admin${number}_id";

                $row[$keyId] = null;
                if ($row[$keyCode]) {
                    $row[$keyId] = $this->getReferenceByAdminCode($row[$keyCode]);
                }

                unset($row[$keyCode]);
            }

            $replaces[] = $row;
            is_callable($progress) && $progress(($pos++) / $max);

            if ($pos%1000) {
                $this->stopwatch->start('replace in loop', 'import');
                $this->databaseImporterTools->replace(GeoName::class, $replaces);
                $replaces = [];
                $this->stopwatch->stop('replace in loop');
            }
        }
        $this->stopwatch->stop('CSV parser');

        $this->stopwatch->start('em flush and clear', 'import');
        $this->em->flush();
        $this->em->commit();
        $this->em->clear();
        $this->stopwatch->stop('em flush and clear');
    }

    public function getCsvReader(File $file): TabularDataReader
    {
        $file2 = $file->unzip();
        $this->setCountLines($file2->getCountLines());

        $csv = Reader::createFromPath($file2->getRealPath());
        $csv->setDelimiter("\t");
        $csv->setHeaderOffset(null);
        $csv->skipEmptyRecords();

        return Statement::create()
            ->process(
                $csv,
                [
                    'id',
                    'name',
                    'ascii_name',
                    'alternatenames',
                    'latitude',
                    'longitude',
                    'feature_class',
                    'feature_code',
                    'country_code',
                    'cc2',
                    'admin1Code',
                    'admin2Code',
                    'admin3Code',
                    'admin4Code',
                    'population',
                    'elevation',
                    'dem',
                    'timezone',
                    'modification_date',
                ]
            );
    }

    public function supports(string $support): bool
    {
        return $support === 'geonames';
    }

    public function getReferenceByCountryCode(string $countryCode): ?int
    {
        if (!isset($this->countryReference[$countryCode])) {
            $table = $this->databaseImporterTools->getTableName(Country::class);
            $id = $this->em->getConnection()->executeStatement(
                'SELECT id FROM '.$table.' WHERE iso = ?',
                [$countryCode]
            );

            $this->countryReference[$countryCode] = $id;
        }

        return $this->countryReference[$countryCode];
    }

    public function getReferenceByAdminCode(string $adminCode): ?int
    {
        if (!isset($this->adminReference[$adminCode])) {
            $table = $this->databaseImporterTools->getTableName(Administrative::class);
            $id = $this->em->getConnection()->executeStatement(
                'SELECT id FROM '.$table.' WHERE code = ?',
                [$adminCode]
            );

            $this->adminReference[$adminCode] = $id;
        }

        return $this->adminReference[$adminCode];
    }

    public function getReferenceByTimezone(string $timezone): ?int
    {
        if (!isset($this->timezoneReference[$timezone])) {
            $table = $this->databaseImporterTools->getTableName(Timezone::class);
            $id = $this->em->getConnection()->executeStatement(
                'SELECT id FROM '.$table.' WHERE timezone = ?',
                [$timezone]
            );

            $this->timezoneReference[$timezone] = $id;
        }

        return $this->timezoneReference[$timezone];
    }
}
