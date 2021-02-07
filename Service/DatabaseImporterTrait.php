<?php

namespace Hotfix\Bundle\GeoNameBundle\Service;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;

final class DatabaseImporterTrait
{
    private EntityManagerInterface $em;
    private array $idGenerator = [];
    private ?SQLLogger $sqlLogger = null;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param object|string $entity
     */
    public function disableAutoIncrement($entity): void
    {
        if (!\is_string($entity)) {
            $entity = \get_class($entity);
        }

        $metadata = $this->em->getClassMetaData($entity);

        if (ClassMetadata::GENERATOR_TYPE_CUSTOM !== $metadata->generatorType) {
            if (!isset($this->idGenerator[$entity])) {
                $this->idGenerator[$entity] = [$metadata->generatorType, $metadata->idGenerator];
            }

            $metadata->setIdGenerator(new AssignedGenerator());
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_CUSTOM);
        }
    }

    /**
     * @param mixed $entity
     */
    public function restoreAutoIncrement($entity): void
    {
        if (!\is_string($entity)) {
            $entity = \get_class($entity);
        }

        if (!isset($this->idGenerator[$entity])) {
            return;
        }

        [$type, $generator] = $this->idGenerator[$entity];
        unset($this->idGenerator[$entity]);

        $metadata = $this->em->getClassMetaData($entity);
        $metadata->setIdGeneratorType($type);
        $metadata->setIdGenerator($generator);
    }

    /**
     * @param object|string $entity
     * @param string[]      $joinTables
     */
    public function truncate($entity, array $joinTables = []): void
    {
        $tableName = $this->getTableName($entity);

        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();

        if ($platform->supportsForeignKeyConstraints()) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        }

        $connection->executeStatement($platform->getTruncateTableSQL($tableName, false));

        foreach ($joinTables as $joinTable) {
            $connection->executeStatement($platform->getTruncateTableSQL($joinTable, false));
        }

        if ($platform->supportsForeignKeyConstraints()) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * @param object|string $entity
     */
    public function getTableName($entity): string
    {
        if (!\is_string($entity)) {
            $entity = \get_class($entity);
        }

        $metadata = $this->em->getClassMetaData($entity);

        return $metadata->getSchemaName() ?? $metadata->getTableName();
    }

    /**
     * @param object|string              $entity
     * @param array<array<string,mixed>> $data
     */
    public function replace($entity, array $data): bool
    {
        $this->disabledLogger();
        $first = $this->getFirstRow($data);

        $columns = $this->getColumns($first);
        $sql = 'REPLACE INTO `' . $this->getTableName($entity) . '` (`' . \implode('`, `', $columns) . '`) VALUES ';
        $sql .= $this->buildQuestionMarks($data);

        $data = $this->inLineArray($data);

        if (0 !== \count($data) % \count($columns)) {
            throw new \LogicException('Insert value list does not match column list');
        }

        $return = $this->em->getConnection()->prepare($sql)->execute($data);

        $this->em->flush();
        $this->em->clear();

        $this->restoreLogger();

        return $return;
    }

    public function disabledLogger(): void
    {
        $this->sqlLogger = $this->em->getConnection()->getConfiguration()->getSQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger();
    }

    private function getFirstRow(array $data): array
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Empty data.');
        }

        [$first,] = $data;

        if (!\is_array($first)) {
            throw new \InvalidArgumentException('$data is not an array of array.');
        }

        return $first;
    }

    private function getColumns($row): array
    {
        $columns = \array_keys($row);

        return \array_map(
            static function ($v) {
                return \str_replace('`', '``', $v);
            },
            $columns
        );
    }

    private function buildQuestionMarks($data): string
    {
        $lines = [];

        foreach ($data as $row) {
            $count = \count($row);
            $questions = [];

            for ($i = 0; $i < $count; ++$i) {
                $questions[] = '?';
            }

            $lines[] = '(' . \implode(',', $questions) . ')';
        }

        return \implode(', ', $lines);
    }

    private function inLineArray(array $data): array
    {
        return \array_merge(...\array_map('array_values', $data));
    }

    public function restoreLogger(): void
    {
        if ($this->sqlLogger) {
            $this->em->getConnection()->getConfiguration()->setSQLLogger($this->sqlLogger);
        }
    }
}
