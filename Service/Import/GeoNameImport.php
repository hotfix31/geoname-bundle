<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;
use Hotfix\Bundle\GeoNameBundle\Entity\Country;
use Hotfix\Bundle\GeoNameBundle\Entity\Feature;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Entity\Timezone;
use Hotfix\Bundle\GeoNameBundle\Service\DatabaseImporterTools;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

class GeoNameImport implements ImportInterface
{
    use GeoNameExistsTrait;

    private EntityManagerInterface $em;
    private DatabaseImporterTools $databaseImporterTools;
    private array $references = [];
    private array $administrativeGeonameIds = [];
    private ?int $countLines = null;

    public function __construct(EntityManagerInterface $em, DatabaseImporterTools $databaseImporterTools)
    {
        $this->em = $em;
        $this->databaseImporterTools = $databaseImporterTools;
    }

    public function import(File $file, ?callable $progress = null): void
    {
        $csv = $this->getCsvReader($file);

        $pos = 0;
        $replaces = [];
        $max = $this->getCountLines();

        $this->em->beginTransaction();

        foreach ($csv as $row) {
            $row = \array_map('trim', $row);
            unset($row['alternatenames']);

            $data = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'ascii_name' => $row['ascii_name'],
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
                'country_code' => $row['country_code'] ?? null,
                'cc2' => '' !== $row['cc2'] ? $row['cc2'] : null,
                'modification_date' => $row['modification_date'] ?? (new \DateTime())->format('Y-m-d'),
                'population' => $row['population'] ? (int) $row['population'] : null,
                'elevation' => $row['elevation'] ? (int) $row['elevation'] : null,
                'dem' => $row['dem'] ? (int) $row['dem'] : null,
            ];

            $data['feature_id'] = $this->getReferenceFeature($row['feature_class'], $row['feature_code']);
            $data['timezone_id'] = $this->getReferenceTimezone($row['timezone']);
            $data['country_id'] = $this->getReferenceCountry($row['country_code']);

            $admCode = [$row['country_code']];

            for ($number = 1; $number < 5; ++$number) {
                $keyCode = "admin${number}Code";
                $keyId = "admin${number}_id";

                $data[$keyId] = null;

                if (!empty($row[$keyCode])) {
                    $admCode[] = $row[$keyCode];
                    $data[$keyId] = $this->getReferenceAdministrative(\implode('.', $admCode));
                }
            }

            $replaces[] = $data;
            \is_callable($progress) && $progress(($pos++) / $max);

            if ($pos % 1000) {
                $this->databaseImporterTools->replace(GeoName::class, $replaces);
                $replaces = [];
            }
        }

        $this->em->flush();
        $this->em->commit();
        $this->em->clear();

        $this->updateAdministrativeTable();
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

    public function getCountLines(): ?int
    {
        return $this->countLines;
    }

    protected function setCountLines(?int $countLines): self
    {
        $this->countLines = $countLines;

        return $this;
    }

    public function getReferenceFeature(string $class, string $code): ?int
    {
        return $this->getReference(
            Feature::class,
            [$class, $code],
            function (string $table, string $class, string $code) {
                return $this->em->getConnection()->executeStatement(
                    'SELECT id FROM ' . $table . ' WHERE class = ? AND code = ?',
                    [$class, $code]
                );
            }
        );
    }

    private function getReference(string $entity, array $keys, callable $resolve): ?int
    {
        if (!isset($this->references[$entity])) {
            $this->references[$entity] = [];
        }

        $hash = \md5(\serialize($keys));

        if (!isset($this->references[$entity][$hash])) {
            $table = $this->databaseImporterTools->getTableName($entity);
            $this->references[$entity][$hash] = (int) $resolve($table, ...$keys);
        }

        return 0 === $this->references[$entity][$hash] ? null : $this->references[$entity][$hash];
    }

    public function getReferenceTimezone(string $timezone): ?int
    {
        return $this->getReference(
            Timezone::class,
            [$timezone],
            function (string $table, string $timezone) {
                return $this->em->getConnection()->executeStatement(
                    'SELECT id FROM ' . $table . ' WHERE timezone = ?',
                    [$timezone]
                );
            }
        );
    }

    public function getReferenceCountry(string $countryCode): ?int
    {
        return $this->getReference(
            Country::class,
            [$countryCode],
            function (string $table, string $countryCode) {
                return $this->em->getConnection()->executeStatement(
                    'SELECT id FROM ' . $table . ' WHERE iso = ?',
                    [$countryCode]
                );
            }
        );
    }

    private function getReferenceAdministrative(string $adminCode): ?int
    {
        return $this->getReference(
            Administrative::class,
            [$adminCode],
            function (string $table, string $adminCode) {
                return $this->em->getConnection()->executeStatement(
                    'SELECT id FROM ' . $table . ' WHERE code = ?',
                    [$adminCode]
                );
            }
        );
    }

    private function updateAdministrativeTable(): void
    {
        if (!$this->administrativeGeonameIds) {
            return;
        }

        $validAdministrativeIds = \array_filter(
            $this->administrativeGeonameIds,
            function (array $row) {
                return $this->geoNameExists($row['geoname_id']);
            }
        );

        $table = $this->databaseImporterTools->getTableName(Administrative::class);
        $stmt = $this->em->getConnection()->prepare('UPDATE ' . $table . ' SET geoname_id = ? WHERE code = ?');

        foreach ($validAdministrativeIds as $row) {
            $stmt->execute([$row['geoname_id'], $row['code']]);
        }
    }

    public function supports(string $support): bool
    {
        return 'geonames' === $support;
    }

    public function addAdministrativeGeonameIds(string $adminCode, int $geonameId): self
    {
        $this->administrativeGeonameIds[$adminCode] = ['code' => $adminCode, 'geoname_id' => $geonameId];

        return $this;
    }
}
