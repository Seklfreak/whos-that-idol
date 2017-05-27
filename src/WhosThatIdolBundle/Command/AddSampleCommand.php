<?php

namespace WhosThatIdolBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use WhosThatIdolBundle\Entity\Subject;
use WhosThatIdolBundle\Utils\Base64ApiSafe;

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

        $subject = new Subject();
        $subject->setName($input->getArgument('idol-english-name'));
        $subject->setGroups(array_map('trim', explode(',', $input->getArgument('group-english-name'))));
        $subject->setFilename($input->getArgument('filename'));
        $subject->setSource('commandline');

        $normalizer = new ObjectNormalizer();
        $normalizer->setIgnoredAttributes(array('picture', 'face'));

        $encoders = array(new JsonEncoder());

        $serializer = new Serializer(array($normalizer), $encoders);

        $subjectJson = $serializer->serialize($subject, 'json');

        if (\file_exists($input->getArgument('filename'))) {
            $fileContent = \file_get_contents($input->getArgument('filename'));

            if ($fileContent === false) {
                $output->writeln("Unable to read file!");
            } else {
                $output->writeln($fileContent);

                $argumentArray =  array(
                    "image" => base64_encode($fileContent),
                    "subject_id" => Base64ApiSafe::base64apisafe_encode($subjectJson),
                    "gallery_name" => $this->getContainer()->getParameter('kairos_gallery_name')
                );
                $response = $kairos->enroll($argumentArray);

                $result = json_decode($response, true);

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