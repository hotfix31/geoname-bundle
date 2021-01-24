<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

interface ImportInterface
{
    public function import(\SplFileObject $file, ?callable $progress = null): void;

    public function supports(string $support): bool;

    public function processRow(array $row): ?object;
}
