<?php

namespace WhosThatIdolBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\SecurityBundle\SecurityUserValueResolver;
use WhosThatIdolBundle\Entity\Subject;
use WhosThatIdolBundle\Entity\TrialUpload;
use Symfony\Component\HttpFoundation\Request;
use WhosThatIdolBundle\Form\TrialUploadForm;
use WhosThatIdolBundle\Utils\Base64ApiSafe;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="frontpage")
     */
    public function indexAction(Request $request)
    {
        $trialUpload = new TrialUpload();
        $form = $this->createForm(TrialUploadForm::class, $trialUpload);
        $form->handleRequest($request);

        $sampleResult = array();
        $imageDisplayWidth = 0;
        $imageDisplayHeight = 0;

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $trialUpload->getIdolPicture();

            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $imageMime = $file->getMimeType();
            list($imageWidth, $imageHeight) = getimagesize($file);

            if ($imageWidth > $imageHeight) {
                $imageDisplayWidth = 600;
                $imageDisplayHeight = $imageHeight * (600 / $imageWidth);
            }
            if ($imageWidth < $imageHeight) {
                $imageDisplayWidth = $imageWidth * (300 / $imageHeight);
                $imageDisplayHeight = 300;
            }
            if ($imageWidth == $imageHeight) {
                $imageDisplayWidth = 600;
                $imageDisplayHeight = 300;
            }

            $file->move(
                $this->getParameter('trial_pictures_directory'),
                $fileName
            );

            $kairos = $this->container->get('app.kairos');

            $movedFilepath = $this->getParameter('trial_pictures_directory') . '/' . $fileName;

            $base64File = \base64_encode(\file_get_contents($movedFilepath));

            $argumentArray = array(
                'image' => $base64File,
                'gallery_name' => $this->getParameter('kairos_gallery_name'),
                'max_num_results' => 30
            );
            $response = $kairos->recognize($argumentArray);

            $responseArray = json_decode($response, true);

            unlink($movedFilepath);

            $sampleResult = array(
                'errorCode' => 0,
                'errorMessage' => '',
                'imageB64' => $base64File,
                'imageMime' => $imageMime,
                'displayWidth' => $imageDisplayWidth,
                'displayHeight' => $imageDisplayHeight,
                'faces' => array()
            );

            $encoders = array(new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());

            $serializer = new Serializer($normalizers, $encoders);

            if (array_key_exists('images', $responseArray) && count($responseArray['images']) >= 1) {
                $persistedSubjectsRepo = $this->getDoctrine()->getRepository('WhosThatIdolBundle:PersistedSubject');

                foreach ($responseArray['images'] as $item) {
                    $subjectName = '';
                    $subjectGroups = array();
                    $confidence = 0;
                    if (array_key_exists('subject_id', $item['transaction']) && array_key_exists('confidence', $item['transaction'])) {
                        $subject = $persistedSubjectsRepo->find($item['transaction']['subject_id']);
                        if ($subject != null) {
                            $subjectName = $subject->getEnglishName();
                            $subjectGroups = $subject->getGroups();
                        }
                        $confidence = round($item['transaction']['confidence'] * 100, 0);
                    }
                    $sampleResult['faces'][] = array(
                        'name' => $subjectName,
                        'groups' => $subjectGroups,
                        'confidence' => $confidence,
                        'width' => $item['transaction']['width'],
                        'height' => $item['transaction']['height'],
                        'topLeftX' => $item['transaction']['topLeftX'],
                        'topLeftY' => $item['transaction']['topLeftY'],
                        'scaledWidth' => round($imageDisplayWidth * ($item['transaction']['width'] / $imageWidth)),
                        'scaledHeight' => round($imageDisplayHeight * ($item['transaction']['height'] / $imageHeight)),
                        'scaledTopLeftX' => round($imageDisplayWidth * ($item['transaction']['topLeftX'] / $imageWidth)),
                        'scaledTopLeftY' => round($imageDisplayHeight * ($item['transaction']['topLeftY'] / $imageHeight))
                    );
                }
            }
            if (array_key_exists('Errors', $responseArray) && count($responseArray['Errors']) >= 1) {
                $sampleResult['errorCode'] = $responseArray['Errors'][0]['ErrCode'];
                $sampleResult['errorMessage'] = $responseArray['Errors'][0]['Message'];
            }
        }


        return $this->render('WhosThatIdolBundle:Default:index.html.twig', array(
            'uploadForm' => $form->createView(),
            'sampleResult' => $sampleResult
        ));
    }
}
