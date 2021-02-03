<?php

namespace Hotfix\Bundle\GeoNameBundle\Command;

use Hotfix\Bundle\GeoNameBundle\Service\Downloader;
use Hotfix\Bundle\GeoNameBundle\Service\File;
use Hotfix\Bundle\GeoNameBundle\Service\Importer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    private const PROGRESS_FORMAT = '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% Mem: %memory:6s% %message%';

    private Downloader $downloader;
    private Importer $importer;
    private string $cacheDir;

    private array $imports = [
        'feature-codes' => [
            'url' => 'https://download.geonames.org/export/dump/featureCodes_en.txt',
            'shortcut' => 'f',
        ],
        'timezones' => [
            'url' => 'https://download.geonames.org/export/dump/timeZones.txt',
            'shortcut' => 't',
        ],
        'admin1' => [
            'url' => 'https://download.geonames.org/export/dump/admin1CodesASCII.txt',
            'shortcut' => 'a1',
        ],
        'admin2' => [
            'url' => 'https://download.geonames.org/export/dump/admin2Codes.txt',
            'shortcut' => 'a2',
        ],
        'geonames' => [
            'url' => 'https://download.geonames.org/export/dump/allCountries.zip',
            'shortcut' => 'g',
        ],
        'countries' => [
            'url' => 'https://download.geonames.org/export/dump/countryInfo.txt',
            'shortcut' => 'c',
        ],
        'hierarchy' => [
            'url' => 'https://download.geonames.org/export/dump/hierarchy.zip',
            'shortcut' => 'i',
        ],
        'alternate-names' => [
            'url' => 'https://download.geonames.org/export/dump/alternateNamesV2.zip',
            'shortcut' => 'l',
        ],
    ];

    public function __construct(Downloader $downloader, Importer $importer, string $cacheDir, string $name = null)
    {
        parent::__construct($name);

        $this->downloader = $downloader;
        $this->importer = $importer;
        $this->cacheDir = $cacheDir;
    }

    protected function configure()
    {
        $this
            ->setName('hotfix:geoname:import')
            ->addOption(
                'download-dir',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Download dir'
            );

        foreach ($this->imports as $name => $options) {
            $this->addOption(
                $name,
                $options['shortcut'],
                InputOption::VALUE_REQUIRED,
                \sprintf('%s files', \ucfirst($name)),
                $options['url']
            );

            $this->addOption(
                \sprintf('skip-%s', $name),
                null,
                InputOption::VALUE_NONE,
                \sprintf('options to skip import %s', $name)
            );
        }
    }

    protected function processDownload(InputInterface $input, OutputInterface $output, string $downloadDir, ?array $batchImport = null): iterable
    {
        foreach ($batchImport ?? $this->imports as $name => $options) {
            if ($input->hasOption('skip-' . $name) && $input->getOption('skip-' . $name)) {
                continue;
            }

            $url = $input->getOption($name);
            $file = $downloadDir . \DIRECTORY_SEPARATOR . \basename($url);

            $this->downloadWithProgressBar($url, $file, $output);
            $output->writeln('');

            yield $name => ['file' => $file, 'options' => $options];
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $downloadDir = $input->getOption('download-dir') ?: $this->cacheDir . \DIRECTORY_SEPARATOR . 'geoname';

        if (!\file_exists($downloadDir) && !\mkdir($downloadDir, 0700, true) && !\is_dir($downloadDir)) {
            $output->writeln('<error>Error on create download directory. (' . $downloadDir . ')</error>');

            return 15;
        }

        if (!\is_writable($downloadDir)) {
            $output->writeln('<error>Error download directory is not writeable. (' . $downloadDir . ')</error>');

            return 20;
        }

        $downloadDir = \realpath($downloadDir);

        foreach ($this->processDownload($input, $output, $downloadDir) as $field => $import) {
            $this->importWithProgressBar($field, $import['file'], $output);
            $output->writeln('');
        }

        return 0;
    }

    public function getProgressBar(OutputInterface $output): ProgressBar
    {
        $progress = new ProgressBar($output, 100);
        $progress->setFormat(self::PROGRESS_FORMAT);
        $progress->setRedrawFrequency(1);

        return $progress;
    }

    public function importWithProgressBar(string $importType, string $filename, OutputInterface $output): void
    {
        $progress = $this->getProgressBar($output);
        $progress->setMessage("Import {$importType}");
        $progress->start();

        $this->importer->import(
            $importType,
            new File($filename, 'r'),
            function ($percent) use ($progress) {
                $progress->setProgress($percent * 100);
            }
        );

        $progress->finish();
    }

    public function downloadWithProgressBar(string $url, string $saveAs, OutputInterface $output): void
    {
        if (\file_exists($saveAs)) {
            $output->write($saveAs . ' exists in the cache.');

            return;
        }

        $progress = $this->getProgressBar($output);
        $progress->setMessage("Download {$url}");
        $progress->start();

        $this->downloader->download(
            $url,
            $saveAs,
            function ($percent) use ($progress) {
                $progress->setProgress((int) ($percent * 100));
            }
        );

        $progress->finish();
    }
}
