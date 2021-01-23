<?php

namespace Hotfix\Bundle\GeoNameBundle\Command;

use Hotfix\Bundle\GeoNameBundle\Service\Downloader;
use Hotfix\Bundle\GeoNameBundle\Service\Importer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    const PROGRESS_FORMAT = '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% Mem: %memory:6s% %message%';

    private Downloader $downloader;
    private Importer $importer;
    private string $cacheDir;

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
                'geoname',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Archive to GeoNames',
                'http://download.geonames.org/export/dump/allCountries.zip'
            )
            ->addOption(
                'timezones',
                't',
                InputOption::VALUE_OPTIONAL,
                'Timezones file',
                'https://download.geonames.org/export/dump/timeZones.txt'
            )
            ->addOption(
                'admin1-codes',
                'a1',
                InputOption::VALUE_OPTIONAL,
                'Admin 1 Codes file',
                'http://download.geonames.org/export/dump/admin1CodesASCII.txt'
            )
            ->addOption(
                'hierarchy',
                'hi',
                InputOption::VALUE_OPTIONAL,
                'Hierarchy ZIP file',
                'http://download.geonames.org/export/dump/hierarchy.zip'
            )
            ->addOption(
                'admin2-codes',
                'a2',
                InputOption::VALUE_OPTIONAL,
                "Admin 2 Codes file",
                'http://download.geonames.org/export/dump/admin2Codes.txt'
            )
            ->addOption(
                'languages-codes',
                'lc',
                InputOption::VALUE_OPTIONAL,
                "Admin 2 Codes file",
                'http://download.geonames.org/export/dump/iso-languagecodes.txt'
            )
            ->addOption(
                'country-info',
                'ci',
                InputOption::VALUE_OPTIONAL,
                "Country info file",
                'http://download.geonames.org/export/dump/countryInfo.txt'
            )
            ->addOption(
                'download-dir',
                'o',
                InputOption::VALUE_OPTIONAL,
                "Download dir",
                null
            )
            ->addOption(
                'skip-admin1-codes',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                false
            )
            ->addOption(
                'skip-admin2-codes',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                false
            )
            ->addOption(
                'kip-geoname',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                false
            )
            ->addOption(
                'skip-hierarchy',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                false
            )
            ->setDescription('Import GeoNames');
    }

    protected function processDownload(InputInterface $input, OutputInterface $output, string $downloadDir): iterable
    {
        $fields = ['timezones'/*, 'country-info', 'admin1-codes', 'admin2-codes', 'geoname', 'country', 'hierarchy'*/];
        foreach ($fields as $field) {
            if ($input->hasOption('skip-'.$field) && $input->getOption('skip-'.$field)) {
                continue;
            }

            $url = $input->getOption($field);
            $file = $downloadDir.DIRECTORY_SEPARATOR.basename($url);

            $this->downloadWithProgressBar($url, $file, $output);
            $output->writeln('');

            yield $field => $file;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $downloadDir = $input->getOption('download-dir') ?: $this->cacheDir.DIRECTORY_SEPARATOR.'geoname';
        if (!file_exists($downloadDir) && !mkdir($downloadDir, 0700, true) && !is_dir($downloadDir)) {
            $output->writeln('<error>Error on create download directory. ('.$downloadDir.')</error>');
            return 15;
        }

        if (!is_writable($downloadDir)) {
            $output->writeln('<error>Error download directory is not writeable. ('.$downloadDir.')</error>');
            return 20;
        }

        $downloadDir = realpath($downloadDir);
        foreach ($this->processDownload($input, $output, $downloadDir) as $field => $file) {
            $this->importWithProgressBar($field, $file, $output);
            $output->writeln('');
        }

        return 0;
        /*$this->importWithProgressBar(
            $this->getContainer()->get("Hotfix.geoname.import.timezone"),
            $timezonesLocal,
            "Importing timezones",
            $output
        )->wait();

        $output->writeln('');

        if (!$input->getOption("skip-admin1")) {
            // admin1
            $admin1 = $input->getOption('admin1-codes');
            $admin1Local = $downloadDir.DIRECTORY_SEPARATOR.basename($admin1);

            $this->downloadWithProgressBar(
                $admin1,
                $admin1Local,
                $output
            )->wait();
            $output->writeln('');

            $this->importWithProgressBar(
                $this->getContainer()->get("Hotfix.geoname.import.administrative"),
                $admin1Local,
                "Importing administrative 1",
                $output
            )->wait();

            $output->writeln('');
        }


        if (!$input->getOption("skip-admin2")) {
            $admin2 = $input->getOption('admin2-codes');
            $admin2Local = $downloadDir.DIRECTORY_SEPARATOR.basename($admin2);


            $this->downloadWithProgressBar(
                $admin2,
                $admin2Local,
                $output
            )->wait();
            $output->writeln('');

            $this->importWithProgressBar(
                $this->getContainer()->get("Hotfix.geoname.import.administrative"),
                $admin2Local,
                "Importing administrative 2",
                $output
            )->wait();


            $output->writeln('');
        }


        if (!$input->getOption("skip-geoname")) {
            // archive
            $archive = $input->getOption('archive');
            $archiveLocal = $downloadDir.DIRECTORY_SEPARATOR.basename($archive);

            $this->downloadWithProgressBar(
                $archive,
                $archiveLocal,
                $output
            )->wait();
            $output->writeln('');

            $this->importWithProgressBar(
                $this->getContainer()->get("Hotfix.geoname.import.geoname"),
                $archiveLocal,
                "Importing GeoNames",
                $output,
                1000
            )->wait();


            $output->writeln("");
        }

        //countries import
        $this->importWithProgressBar(
            $this->getContainer()->get("Hotfix.geoname.import.country"),
            $countryInfoLocal,
            "Importing Countries",
            $output
        )->wait();


        if (!$input->getOption("skip-hierarchy")) {
            // archive
            $archive = $input->getOption('hierarchy');
            $archiveLocal = $downloadDir.DIRECTORY_SEPARATOR.basename($archive);

            $this->downloadWithProgressBar(
                $archive,
                $archiveLocal,
                $output
            )->wait();
            $output->writeln('');

            $this->importWithProgressBar(
                $this->getContainer()->get("Hotfix.geoname.import.hierarchy"),
                $archiveLocal,
                "Importing Hierarchy",
                $output,
                1000
            )->wait();


            $output->writeln("");
        }


        $output->writeln("");


        $output->writeln("Imported successfully! Thank you :) ");

        return 0;
*/
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
            new \SplFileObject($filename, 'r'),
            function ($percent) use ($progress) {
                $progress->setProgress((int)($percent * 100));
            }
        );

        $progress->finish();
    }

    public function downloadWithProgressBar(string $url, string $saveAs, OutputInterface $output): void
    {
        /*if (file_exists($saveAs)) {
            $output->writeln($saveAs." exists in the cache.");

            return;
        }*/

        $progress = $this->getProgressBar($output);
        $progress->setMessage("Download {$url}");
        $progress->start();

        $this->downloader->download(
            $url,
            $saveAs,
            function ($percent) use ($progress) {
                $progress->setProgress((int)($percent * 100));
            }
        );

        $progress->finish();
    }
}
