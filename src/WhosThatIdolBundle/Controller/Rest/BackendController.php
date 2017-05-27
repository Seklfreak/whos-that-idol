<?php

namespace WhosThatIdolBundle\Controller\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use WhosThatIdolBundle\Entity\Subject;
use WhosThatIdolBundle\Form\SubjectForm;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use WhosThatIdolBundle\Utils\Base64ApiSafe;

class BackendController extends FOSRestController
{
    /**
     * @Rest\View
     * @Route("/delete_subject.{_format}", name="api_delete_subject", defaults={"_format"="json"})
     * @Method("POST")
     */
    public function deleteSubject(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            $view = $this->view('Not authenticated', 401);
            return $this->handleView($view);
        }

        if (array_key_exists('subject-id', $request->request->all())) {
            $subjectID = $request->request->all()['subject-id'];

            $subject = $this->getDoctrine()->getRepository('WhosThatIdolBundle:Subject')->find($subjectID);

            if ($subject !== null) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($subject);
                $em->flush();

                $view = $this->view('Removed subject', 200);
                return $this->handleView($view);
            } else {
                $view = $this->view('Unable to find subject', 404);
                return $this->handleView($view);
            }
        } else {
            $view = $this->view('Please pass an ID', 400);
            return $this->handleView($view);
        }
    }

    /**
     * @Rest\View
     * @Route("/accept_subject.{_format}", name="api_accept_subject", defaults={"_format"="json"})
     * @Method("POST")
     */
    public function acceptSubject(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            $view = $this->view('Not authenticated', 401);
            return $this->handleView($view);
        }

        if (array_key_exists('subject-id', $request->request->all()) &&
            array_key_exists('subject-name', $request->request->all()) &&
            array_key_exists('subject-groups', $request->request->all())) {
            $subjectID = $request->request->all()['subject-id'];
            $subjectÍdolName = $request->request->all()['subject-name'];
            $subjectGroupName = $request->request->all()['subject-groups'];

            $subject = $this->getDoctrine()->getRepository('WhosThatIdolBundle:Subject')->find($subjectID);

            if ($subject !== null) {
                $subject->setName($subjectÍdolName);
                $subject->setGroups($subjectGroupName);

                $validator = $this->get('validator');
                $errors = $validator->validate($subject);

                if (count($errors) <= 0) {
                    $normalizer = new ObjectNormalizer();
                    $normalizer->setIgnoredAttributes(array('picture', 'face'));
                    $encoders = array(new JsonEncoder());

                    $serializer = new Serializer(array($normalizer), $encoders);

                    $subjectJson = $serializer->serialize($subject, 'json');

                    $faceParts = explode(',', $subject->getFace());

                    $argumentArray =  array(
                        "image" => $faceParts[1],
                        "subject_id" => Base64ApiSafe::base64apisafe_encode($subjectJson),
                        "gallery_name" => $this->getParameter('kairos_gallery_name')
                    );

                    $kairos = $this->get('app.kairos');

                    $response = $kairos->enroll($argumentArray);

                    $result = json_decode($response, true);

                    if (array_key_exists('images', $result) && count($result['images']) >= 1) {
                        $em = $this->getDoctrine()->getManager();
                        $em->remove($subject);
                        $em->flush();

                        $view = $this->view('Added and removed from queue', 200);
                        return $this->handleView($view);
                    }
                    if (array_key_exists('Errors', $result) && count($result['Errors']) >= 1) {
                        $view = $this->view($result['Errors'][0]['Message'], 500);
                        return $this->handleView($view);
                    }
                } else {
                }
                $view = $this->view('Invalid data', 400);
                return $this->handleView($view);
            } else {
                $view = $this->view('Unable to find subject', 404);
                return $this->handleView($view);
            }
        } else {
            $view = $this->view('Please pass an ID, Idol Name and Group Name', 400);
            return $this->handleView($view);
        }
    }
}