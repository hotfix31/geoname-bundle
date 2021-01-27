<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;
use Hotfix\Bundle\GeoNameBundle\Entity\Country;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Entity\Timezone;
use Hotfix\Bundle\GeoNameBundle\Service\DbalExtension;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;
use Symfony\Component\Stopwatch\Stopwatch;

class GeoNameImport extends ImportAbstract
{
    protected DbalExtension $dbalExtension;
    protected array $adminReference = [];
    protected array $countryReference = [];
    protected array $timezoneReference = [];
    protected int $flushModulo = 100;
    private Stopwatch $stopwatch;

    public function __construct(EntityManagerInterface $em, DbalExtension $dbalExtension, Stopwatch $stopwatch)
    {
        parent::__construct($em);
        $this->dbalExtension = $dbalExtension;
        $this->stopwatch = $stopwatch;
    }

    private function count(\SplFileObject $file): int
    {
        $counter = 0;
        while (!$file->eof()) {
            $file->fgets();
            $counter++;
        }

        $file->seek(0);

        return $counter;
    }

    public function import(\SplFileObject $file, ?callable $progress = null): void
    {
        $this->stopwatch->start('CSV Reader', 'import');
        //$file->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $csv = $this->getCsvReader($file);
        $this->stopwatch->stop('CSV Reader');
        //$max = 100;
        $this->stopwatch->start('CSV counter', 'import');
        $max = $this->count($file);
        $this->stopwatch->stop('CSV counter');

        $pos = 0;
        $replaces = [];
        $this->em->beginTransaction();

        $this->stopwatch->start('CSV parser', 'import');
        foreach ($csv as $row) {
            $row = array_map('trim', $row);
            unset($row['alternatenames']);

            $row['country_id'] = $this->getReferenceByCountryCode($row['country_code']);

            $row['timezone_id'] = $this->getReferenceByCountryCode($row['timezone']);
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

            if ($pos % $this->flushModulo) {
                $this->stopwatch->start('replace in loop', 'import');
                $this->dbalExtension->replace(
                    $this->getTableName(GeoName::class),
                    $replaces
                );

                $this->em->flush();
                $this->em->clear();

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

    public function getCsvReader(\SplFileObject $file): TabularDataReader
    {
        $filenameUnzip = str_replace('.zip', '.txt', $file->getRealPath());
        if (!file_exists($filenameUnzip) || filectime($filenameUnzip) < $file->getCTime()) {
            $zip = new \ZipArchive();
            $zip->open($file->getRealPath());
            $zip->extractTo(dirname($file->getRealPath()), [$file->getBasename('.zip').'.txt']);
            $zip->close();
        }

        $csv = Reader::createFromPath($filenameUnzip, 'r');
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

    public function processRow(array $row): ?object
    {
        // not implemented here
        return new \stdClass();
    }

    public function getReferenceByCountryCode(string $countryCode): ?int
    {
        if (!isset($this->countryReference[$countryCode])) {
            $table = $this->getTableName(Country::class);
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
            $table = $this->getTableName(Administrative::class);
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
            $table = $this->getTableName(Timezone::class);
            $id = $this->em->getConnection()->executeStatement(
                'SELECT id FROM '.$table.' WHERE timezone = ?',
                [$timezone]
            );

            $this->timezoneReference[$timezone] = $id;
        }

        return $this->timezoneReference[$timezone];
    }
}
