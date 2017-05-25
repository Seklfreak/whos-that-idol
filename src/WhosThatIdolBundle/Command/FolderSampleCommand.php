<?php

namespace WhosThatIdolBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Finder\Finder;

class FolderSampleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wti:sample:folder')
            ->setDescription('Adds new samples from folder.')
            ->setHelp('Add new samples from folder structure /group/name/pictures.')
            ->addArgument('folder', InputArgument::REQUIRED, '.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $finder->files()->in($input->getArgument('folder'));

        $kairos = $this->getContainer()->get('app.kairos');

        foreach ($finder as $file) {
            $pathParts = pathinfo($file->getRealPath());

            $pathFolders = preg_split( "/(\\\|\/)/", $pathParts['dirname']);

            $groupName = $pathFolders[count($pathFolders)-2];
            $idolName = $pathFolders[count($pathFolders)-1];

            $output->writeln(
                'Adding: ' .
                $idolName . ' of ' .
                $groupName . ' ' .
                'from file: ' . $file->getRealPath()
            );

            $subjectId = str_replace('.', '', str_replace('/', '-', $idolName . ' of ' .
                $groupName));

            if (\file_exists($file->getRealPath())) {
                $fileContent = \file_get_contents($file->getRealPath());

                if ($fileContent === false) {
                    $output->writeln("Unable to read file!");
                } else {
                    $argumentArray = array(
                        "image" => base64_encode($fileContent),
                        "subject_id" => $subjectId,
                        "gallery_name" => $this->getContainer()->getParameter('kairos_gallery_name')
                    );
                    $response = $kairos->enroll($argumentArray);

                    $result = json_decode($response, true);

                    if (array_key_exists('Errors', $result)) {
                        var_dump($result);
                    } else {
                        $output->writeln("Status: " . $result["images"][0]["transaction"]["status"]);
                    }
                }
            } else {
                $output->writeln("File not found!");
            }
        }
    }
}