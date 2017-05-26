<?php

namespace WhosThatIdolBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WhosThatIdolBundle\Entity\TrialUpload;
use Symfony\Component\HttpFoundation\Request;
use WhosThatIdolBundle\Form\TrialUploadForm;

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

            if (array_key_exists('images', $responseArray) && count($responseArray['images']) >= 1) {
                foreach ($responseArray['images'] as $item) {
                    $id = '';
                    $confidence = 0;
                    if (array_key_exists('subject_id', $item['transaction'])) {
                        $id = $item['transaction']['subject_id'];
                        $confidence = round($item['transaction']['confidence'] * 100, 0);
                    }
                    $sampleResult['faces'][] = array(
                        'id' => $id,
                        'confidence' => $confidence,
                        'width' => round($imageDisplayWidth * ($item['transaction']['width'] / $imageWidth)),
                        'height' => round($imageDisplayHeight * ($item['transaction']['height'] / $imageHeight)),
                        'topLeftX' => round($imageDisplayWidth * ($item['transaction']['topLeftX'] / $imageWidth)),
                        'topLeftY' => round($imageDisplayHeight * ($item['transaction']['topLeftY'] / $imageHeight))
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
