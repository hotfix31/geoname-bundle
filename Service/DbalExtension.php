<?php

namespace Hotfix\Bundle\GeoNameBundle\Service;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\ORM\EntityManagerInterface;

class DbalExtension
{
    private EntityManagerInterface $entityManager;
    private ?SQLLogger $sqlLogger = null;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function disabledLogger(): void
    {
        $this->sqlLogger = $this->entityManager->getConnection()->getConfiguration()->getSQLLogger();
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger();
    }

    protected function restoreLogger(): void
    {
        if ($this->sqlLogger) {
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger($this->sqlLogger);
        }
    }

    /**
     * @param array<array<string,mixed>> $data
     */
    public function replace(string $tableExpression, array $data): bool
    {
        $this->disabledLogger();
        $first = $this->getFirstRow($data);

        $columns = '`'.implode('`,`', $this->getColumns($first)).'`';

        $sql = 'REPLACE INTO `'.$tableExpression.'` ('.$columns.') VALUES ';
        $sql .= $this->buildQuestionMarks($data);

        $data = $this->inLineArray($data);
        $stmt = $this->entityManager->getConnection()->prepare($sql);

        $return = $stmt->execute($data);
        $this->restoreLogger();

        return $return;
    }

    private static function inLineArray(array $data): array
    {
        return array_merge(...array_map('array_values', $data));
    }

    private function buildQuestionMarks($data): string
    {
        $lines = [];
        foreach ($data as $row) {
            $count = count($row);
            $questions = [];
            for ($i = 0; $i < $count; ++$i) {
                $questions[] = '?';
            }

            $lines[] = '('.implode(',', $questions).')';
        }

        return implode(', ', $lines);
    }

    private function getColumns($row): array
    {
        $columns = array_keys($row);

        return array_map(
            static function ($v) {
                return str_replace('`', '``', $v);
            },
            $columns
        );
    }

    private function getFirstRow(array $data): array
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Empty data.');
        }

        [$first,] = $data;
        if (!is_array($first)) {
            throw new \InvalidArgumentException('$data is not an array of array.');
        }

        return $first;
    }
}