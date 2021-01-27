<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Entity\Hierarchy;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;

class HierarchyImport extends ImportAbstract
{
    protected function getCsvReader(File $file): TabularDataReader
    {
        $this->databaseImporterTools->truncate(Hierarchy::class);

        $file2 = $file->unzip();
        $csv = parent::getCsvReader($file2);
        if ($csv instanceof Reader) {
            $csv->setHeaderOffset(null);
        }

        return Statement::create()
            ->process($csv, ['parentId', 'childId', 'type']);
    }

    /**
     * @return Hierarchy|null
     */
    protected function processRow(array $row): ?object
    {
        $hierarchy = new Hierarchy();
        $hierarchy->setParent($this->em->getReference(GeoName::class, $row['parentId']));
        $hierarchy->setParent($this->em->getReference(GeoName::class, $row['childId']));

        return $hierarchy;
    }
//    {
//
//        $avrOneLineSize = 29.4;
//        $batchSize = 10000;
//
//        if($batchSize > 1){ //temporarly
//            return true;
//        }
//        $connection = $this->em->getConnection();
//
//        $fileInside = basename($filePath, ".zip") . '.txt';
//        $handler = fopen("zip://{$filePath}#{$fileInside}", 'r');
//        $max = (int)filesize($filePath) / $avrOneLineSize;
//
//        $fieldsNames = $this->getFieldNames();
//
//        $geoNameTableName = $this->em
//            ->getClassMetadata("HotfixGeoNameBundle:GeoName")
//            ->getTableName();
//
//        $timezoneTableName = $this->em
//            ->getClassMetadata("HotfixGeoNameBundle:Timezone")
//            ->getTableName();
//
//        $administrativeTableName = $this->em
//            ->getClassMetadata("HotfixGeoNameBundle:Administrative")
//            ->getTableName();
//
//
//        $dbType = $connection->getDatabasePlatform()->getName();
//
//        $connection->exec("START TRANSACTION");
//
//        $pos = 0;
//
//        $buffer = [];
//
//        $queryBuilder = $connection->createQueryBuilder()
//            ->insert($geoNameTableName);
//
//        while (!feof($handler)) {
//            $csv = fgetcsv($handler, null, "\t");
//            if (!is_array($csv)) {
//                continue;
//            }
//            if (!isset($csv[0]) || !is_numeric($csv[0])) {
//                continue;
//            }
//
//            $row = array_map('trim', $csv);
//
//            if(!isset($row[0]) || !isset($row[1])){
//                continue;
//            }
//
//            $geoNameId = $row[0];
//            $geoNameId2 = $row[1];
//            $geoNameId2 = isset($row[3]) ? $row[3] : null;
//            $geoNameId2 = isset($row[4]) ? $row[4] : null;
//
//
//            $query = $queryBuilder->values([
//
//            ]);
//
//
//            $buffer[] = $this->insertToReplace($query, $dbType);
//
//            $pos++;
//
//            if ($pos % $batchSize) {
//                $this->save($buffer);
//                $buffer = [];
//                is_callable($progress) && $progress(($pos) / $max);
//            }
//
//        }
//
//        !empty($buffer) &&  $this->save($buffer);
//        $connection->exec('COMMIT');
//
//        return true;
//    }
//
    public function supports(string $support): bool
    {
        return $support === 'hierarchy';
    }
}
