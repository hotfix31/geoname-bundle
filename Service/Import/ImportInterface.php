<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

interface ImportInterface
{
    public function import(\SplFileObject $file, ?callable $progress = null);

    public function supports(string $support): bool;
}
