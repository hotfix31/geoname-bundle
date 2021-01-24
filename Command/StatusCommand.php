<?php

namespace Hotfix\Bundle\GeoNameBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;
use Hotfix\Bundle\GeoNameBundle\Entity\Country;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Entity\Hierarchy;
use Hotfix\Bundle\GeoNameBundle\Entity\Timezone;
use Hotfix\Bundle\GeoNameBundle\Repository\HierarchyRepository;
use Hotfix\Bundle\GeoNameBundle\Service\Downloader;
use Hotfix\Bundle\GeoNameBundle\Service\Importer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StatusCommand extends Command
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        $this->em = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('hotfix:geoname:status')
            ->setDescription('Status of import GeoNames data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = [];
        $entities = [Timezone::class, Administrative::class, Country::class, GeoName::class, Hierarchy::class];
        foreach ($entities as $entity) {
            $repository = $this->em->getRepository($entity);

            $table[] = [$entity, $repository->count([])];
        }

        $io = new SymfonyStyle($input, $output);
        $io->title('Status of import GeoName\'s data');
        $io->table(['Entity', 'counter'], $table);

        return 0;
    }

}
