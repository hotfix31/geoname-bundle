<?php

namespace Hotfix\Bundle\GeoNameBundle\Service;

use League\Csv\Reader;

class File extends \SplFileObject
{
    protected ?int $countLines = null;

    public function __construct($file_name, $open_mode = 'r', $use_include_path = false, $context = null)
    {
        parent::__construct($file_name, $open_mode, $use_include_path, $context);
        $this->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
    }

    public function getCountLines(): int
    {
        if (null === $this->countLines) {
            if (!$this->isReadable()) {
                throw new \LogicException('file cannot be read.');
            }

            $this->seek(0);
            $this->countLines = 0;

            while ($this->valid()) {
                try {
                    $this->fgets();
                    ++$this->countLines;
                } catch (\RuntimeException $e) {
                    // continue
                }
            }
        }

        return $this->countLines;
    }

    public function unzip(string $mode = 'r'): self
    {
        if ('zip' !== $this->getExtension()) {
            throw new \LogicException('unzip method works only with zip file.');
        }

        $filenameUnzip = \str_replace('.zip', '.txt', $this->getRealPath());

        if (!\file_exists($filenameUnzip) || \filectime($filenameUnzip) < $this->getCTime()) {
            $zip = new \ZipArchive();
            $zip->open($this->getRealPath());
            $zip->extractTo(\dirname($this->getRealPath()), [$this->getBasename('.zip') . '.txt']);
            $zip->close();
        }

        return new static($filenameUnzip, $mode);
    }

    public function getCsvReader(): Reader
    {
        $csv = Reader::createFromFileObject($this);
        $csv->setDelimiter("\t");
        $csv->setHeaderOffset(0);
        $csv->skipEmptyRecords();

        return $csv;
    }
}
