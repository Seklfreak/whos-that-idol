<?php

namespace WhosThatIdolBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ClearSampleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wti:sample:clear')
            ->setDescription('Removes all sample images.')
            ->setHelp('Removes all sample images from the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            'Clearing database'
        );

        $kairos = $this->getContainer()->get('app.kairos');

        $argumentArray = array(
            "gallery_name" => $this->getContainer()->getParameter('kairos_gallery_name')
        );
        $response = $kairos->removeGallery($argumentArray);

        $result = json_decode($response, true);

        $output->writeln("Status: " . $result["status"]);
    }
}