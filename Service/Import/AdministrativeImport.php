<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Repository\AdministrativeRepository;
use Hotfix\Bundle\GeoNameBundle\Service\DatabaseImporterTools;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

class AdministrativeImport extends ImportAbstract
{
    use GeoNameExistsTrait;

    protected GeoNameImport $geoNameImport;
    protected ?AdministrativeRepository $repository = null;

    public function __construct(GeoNameImport $geoNameImport, EntityManagerInterface $em, DatabaseImporterTools $databaseImporterTools)
    {
        $this->geoNameImport = $geoNameImport;
        parent::__construct($em, $databaseImporterTools);
    }

    protected function getCsvReader(File $file): TabularDataReader
    {
        $csv = parent::getCsvReader($file);

        if ($csv instanceof Reader) {
            $csv->setHeaderOffset(null);
        }

        return Statement::create()
            ->process($csv, ['code', 'name', 'asciiName', 'geoNameId']);
    }

    protected function getRepository(): AdministrativeRepository
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository(Administrative::class);
        }

        return $this->repository;
    }

    protected function findByCodeOrCreateNew(string $code): Administrative
    {
        return $this->getRepository()->findOneByCode($code) ?? new Administrative();
    }

    /**
     * @return Administrative
     */
    protected function processRow(array $row): ?object
    {
        [$code, $name, $asciiName, $geonameId] = \array_values($row);

        $object = $this->findByCodeOrCreateNew($code);
        $object->setCode($code);
        $object->setName($name);
        $object->setAsciiName($asciiName);

        $geonameId = $geonameId ? (int) $geonameId : null;

        if ($geonameId && $this->geoNameExists($geonameId)) {
            $object->setGeoName($this->em->getReference(GeoName::class, $geonameId));
        } else {
            $this->geoNameImport->addAdministrativeGeonameIds($code, $geonameId);
        }

        return $object;
    }

    public function supports(string $support): bool
    {
        return 0 === \strpos($support, 'admin');
    }
}
