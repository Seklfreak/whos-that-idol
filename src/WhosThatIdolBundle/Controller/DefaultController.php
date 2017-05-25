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

        $idolsFound = array();
        $base64File = "";
        $base64Mime = "";
        $imageWidth = 0;
        $imageHeight = 0;
        $imageDisplayWidth = 0;
        $imageDisplayHeight = 0;

        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded PDF file
            $file = $trialUpload->getIdolPicture();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $base64Mime = $file->getMimeType();
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

            // Move the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('trial_pictures_directory'),
                $fileName
            );

            $kairos = $this->container->get('app.kairos');
            //$response = $kairos->viewGalleries();

            $movedFilepath = $this->getParameter('trial_pictures_directory') . '/' . $fileName;

            $base64File = \base64_encode(\file_get_contents($movedFilepath));

            $argumentArray = array(
                'image' => $base64File,
                'gallery_name' => $this->getParameter('kairos_gallery_name')
            );
            $response = $kairos->recognize($argumentArray);

            $responseArray = json_decode($response, true);

            unlink($movedFilepath);

            if (array_key_exists('images', $responseArray)) {
                foreach ($responseArray['images'] as $item) {
                    if ($item['transaction']['status'] == 'success') {
                        $idolsFound[] = array(
                            'id' => $item['transaction']['subject_id'],
                            'confidence' => round($item['transaction']['confidence']*100, 0),
                            'width' => round($imageDisplayWidth * ($item['transaction']['width'] / $imageWidth)),
                            'height' => round($imageDisplayHeight * ($item['transaction']['height'] / $imageHeight)),
                            'topLeftX' => round($imageDisplayWidth * ($item['transaction']['topLeftX'] / $imageWidth)),
                            'topLeftY' =>  round($imageDisplayHeight * ($item['transaction']['topLeftY'] / $imageHeight))
                        );
                    }
                }
            }

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            //$trialUpload->setIdolPicture($fileName);
        }


        return $this->render('WhosThatIdolBundle:Default:index.html.twig', array(
            'uploadForm' => $form->createView(),
            'idolsFound' => $idolsFound,
            'base64File' => $base64File,
            'base64Mime' => $base64Mime,
            'displayWidth' => $imageDisplayWidth,
            'displayHeight' => $imageDisplayHeight
        ));
    }
}
