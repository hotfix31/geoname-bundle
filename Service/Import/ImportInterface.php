<?php

namespace Hotfix\Bundle\GeoNameBundle\Service\Import;

use Hotfix\Bundle\GeoNameBundle\Service\File;

interface ImportInterface
{
    public function import(File $file, ?callable $progress = null): void;

    public function supports(string $support): bool;
}
