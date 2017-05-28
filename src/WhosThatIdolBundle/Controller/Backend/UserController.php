<?php

namespace WhosThatIdolBundle\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use WhosThatIdolBundle\Entity\Subject;

class UserController extends Controller
{

    /**
     * @Route("/account/queue", name="backend_queue")
     */
    public function queueAction(Request $request)
    {
        $subjects = $this->getDoctrine()->getRepository('WhosThatIdolBundle:Subject')->findAll();
        return $this->render('WhosThatIdolBundle:Backend:queue.html.twig', array(
            'subjects' => $subjects
        ));
    }

    /**
     * @Route("/account/database", name="backend_subjects")
     */
    public function subjectAction(Request $request)
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $kairos = $this->container->get('app.kairos');

        $response = $kairos->viewSubjectsInGallery(array(
            'gallery_name' => $this->getParameter('kairos_gallery_name')
        ));
        $responseArray = json_decode($response, true);

        $persistedSubjectsRepo = $this->getDoctrine()->getRepository('WhosThatIdolBundle:PersistedSubject');
        $subjectsResult = array();

        if (array_key_exists('subject_ids', $responseArray) && count($responseArray['subject_ids']) >= 1) {
            foreach ($responseArray['subject_ids'] as $subjectId) {
                $subjectsResult[] = $persistedSubjectsRepo->find($subjectId);
            }
        }
        if (array_key_exists('Errors', $responseArray) && count($responseArray['Errors']) >= 1) {
            $subjectsResult['errorCode'] = $responseArray['Errors'][0]['ErrCode'];
            $subjectsResult['errorMessage'] = $responseArray['Errors'][0]['Message'];
        }

        return $this->render('WhosThatIdolBundle:Backend:subjects.html.twig', array(
            'subjects' => $subjectsResult
        ));
    }
}