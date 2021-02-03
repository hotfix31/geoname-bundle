<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\Feature;
use Hotfix\Bundle\GeoNameBundle\Repository\FeatureRepository;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

class FeatureImport extends ImportAbstract
{
    protected ?FeatureRepository $repository = null;

    protected function getCsvReader(File $file): TabularDataReader
    {
        $csv = parent::getCsvReader($file);

        if ($csv instanceof Reader) {
            $csv->setHeaderOffset(null);
        }

        return Statement::create()
            ->process($csv, ['featureCode', 'name', 'description']);
    }

    protected function getRepository(): FeatureRepository
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository(Feature::class);
        }

        return $this->repository;
    }

    protected function findByFeatureCodeOrCreateNew(string $class, string $code): Feature
    {
        return $this->getRepository()->findOneByFeatureCode($class, $code) ?? new Feature();
    }

    public function supports(string $support): bool
    {
        return 'feature-codes' === $support;
    }

    /**
     * @return Feature|null
     */
    protected function processRow(array $row): ?object
    {
        if ('null' === $row['featureCode']) {
            return null;
        }

        [$featureCode, $name, $description] = \array_values($row);
        [$class, $code] = \explode('.', $featureCode);

        $object = $this->findByFeatureCodeOrCreateNew($class, $code);
        $object->setClass($class);
        $object->setCode($code);
        $object->setName($name);
        $object->setDescription($description);

        return $object;
    }
}
