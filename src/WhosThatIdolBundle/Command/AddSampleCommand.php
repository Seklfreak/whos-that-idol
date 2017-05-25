<?php

namespace WhosThatIdolBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class AddSampleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wti:sample:add')
            ->setDescription('Adds a new sample image.')
            ->setHelp('Add a new sample image to the database.')
            ->addArgument('idol-english-name', InputArgument::REQUIRED, '.')
            ->addArgument('group-english-name', InputArgument::REQUIRED, '.')
            ->addArgument('filename', InputArgument::REQUIRED, '.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            'Adding: '.
            $input->getArgument('idol-english-name').' of '.
            $input->getArgument('group-english-name').' '.
            'from file: '.$input->getArgument('filename')
        );

        $kairos = $this->getContainer()->get('app.kairos');

        $subjectId = str_replace('.', '', str_replace('/', '-', $input->getArgument('idol-english-name').' of '.
            $input->getArgument('group-english-name')));

        if (\file_exists($input->getArgument('filename'))) {
            $fileContent = \file_get_contents($input->getArgument('filename'));

            if ($fileContent === false) {
                $output->writeln("Unable to read file!");
            } else {
                $output->writeln($fileContent);

                $argumentArray =  array(
                    "image" => base64_encode($fileContent),
                    "subject_id" => $subjectId,
                    "gallery_name" => $this->getContainer()->getParameter('kairos_gallery_name')
                );
                $response = $kairos->enroll($argumentArray);

                $result = json_decode($response, true);

                $output->writeln("Status: ".$result["images"][0]["transaction"]["status"]);
            }
        } else {
            $output->writeln("File not found!");
        }
    }
}