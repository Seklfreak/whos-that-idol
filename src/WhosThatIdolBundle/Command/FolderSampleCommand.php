<?php

namespace WhosThatIdolBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Finder\Finder;
use WhosThatIdolBundle\Entity\Subject;

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

            $subject = new Subject();
            $subject->setName($idolName);
            $subject->setGroups(array_map('trim', explode(',', $groupName)));
            $subject->setFilename($file->getRealPath());
            $subject->setSource('commandline');

            $persistedSubject = $this->getContainer()->get('doctrine')
                ->getRepository('WhosThatIdolBundle:PersistedSubject')
                ->getByEnglishNameAndGroups($subject->getName(), $subject->getGroups());

            if (\file_exists($file->getRealPath())) {
                $fileContent = \file_get_contents($file->getRealPath());

                if ($fileContent === false) {
                    $output->writeln("Unable to read file!");
                } else {
                    $argumentArray = array(
                        "image" => base64_encode($fileContent),
                        "subject_id" => $persistedSubject->getId(),
                        "gallery_name" => $this->getContainer()->getParameter('kairos_gallery_name')
                    );
                    $response = $kairos->enroll($argumentArray);

                    $result = json_decode($response, true);

                    $output->writeln($response);
                    if (array_key_exists('images', $result) && count($result['images']) >= 1) {
                        $output->writeln("Status: ".$result["images"][0]["transaction"]["status"]);
                    }
                    if (array_key_exists('Errors', $result) && count($result['Errors']) >= 1) {
                        $output->writeln("Error: ".$result['Errors'][0]['Message']);
                    }
                }
            } else {
                $output->writeln("File not found!");
            }
        }
    }
}