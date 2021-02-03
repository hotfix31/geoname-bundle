<?php

namespace Hotfix\Bundle\GeoNameBundle\Service;

use Hotfix\Bundle\GeoNameBundle\Service\Import\ImportInterface;

class Importer
{
    /**
     * @var ImportInterface[]
     */
    private iterable $imports;

    public function __construct(iterable $imports)
    {
        $this->imports = $imports;
    }

    public function import(string $importSupport, \SplFileObject $file, ?callable $progress = null): void
    {
        foreach ($this->imports as $import) {
            if (!$import->supports($importSupport)) {
                continue;
            }

            $import->import($file, $progress);
        }
    }
}
