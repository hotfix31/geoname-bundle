<?php

namespace Hotfix\Bundle\GeoNameBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Hotfix\Bundle\GeoNameBundle\Entity\Administrative;
use Hotfix\Bundle\GeoNameBundle\Entity\AlternateName;
use Hotfix\Bundle\GeoNameBundle\Entity\Country;
use Hotfix\Bundle\GeoNameBundle\Entity\Feature;
use Hotfix\Bundle\GeoNameBundle\Entity\GeoName;
use Hotfix\Bundle\GeoNameBundle\Entity\Hierarchy;
use Hotfix\Bundle\GeoNameBundle\Entity\Timezone;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCommand extends ImportCommand
{
    protected EntityManagerInterface $em;

    protected array $imports = [
        'geonames-modification' => [
            'url' => 'https://download.geonames.org/export/dump/modifications-%s.txt',
            'shortcut' => 'm',
        ],
        'geonames-delete' => [
            'url' => 'https://download.geonames.org/export/dump/deletes-%s.txt',
            'shortcut' => 'd',
        ],
    ];

    protected function configure()
    {
        parent::configure();
        $this->setName('hotfix:geoname:update');
    }

    protected function getUrl(InputInterface $input, string $name): string
    {
        $url = parent::getUrl($input, $name);
        if (strpos($url, '%s') !== false) {
            $yesterday = new \DateTime('-1 day');
            $url = sprintf($url, $yesterday->format('Y-m-d'));
        }

        return $url;
    }
}
