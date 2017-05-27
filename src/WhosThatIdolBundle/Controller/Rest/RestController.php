<?php

namespace WhosThatIdolBundle\Controller\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use WhosThatIdolBundle\Entity\Subject;
use WhosThatIdolBundle\Form\SubjectForm;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RestController extends FOSRestController
{
    /**
     * @Rest\View
     * @Route("/new_subject.{_format}", name="api_new_subject", defaults={"_format"="json"})
     * @Method("POST")
     */
    public function newSubject(Request $request)
    {
        $subject = new Subject();
        //$statusCode = $subject->isNew() ? 201 : 204;
        $form = $this->createForm(SubjectForm::class, $subject);
        //$form->handleRequest($request);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            if (strlen($subject->getPicture())*8 > 1000000) {
                // bigger than 1 MB?
                $subject->setPicture('');
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($subject);
            $em->flush();

            // set the `Location` header only when creating new resources
//            if (201 === $statusCode) {
//                $response->headers->set('Location',
//                    $this->generateUrl(
//                        'acme_demo_user_get', array('id' => $subject->getId()),
//                        true // absolute
//                    )
//                );
//            }

            //return $response;
            $view = $this->view($form, 200);
            return $this->handleView($view);
        }

        $view = $this->view($form, 400);
        return $this->handleView($view);
    }

//    /**
//     * @Rest\View
//     * @Route("/list_subjects.{_format}", name="api_list_subjects", defaults={"_format"="json"})
//     * @Method("GET")
//     */
//    public function listSubjects()
//    {
///*        $subject = new Subject();
//        $subject->setName("Name A");
//        $subject->setGroups(array("Group A", "Group B"));
//        $subject->setSource("web");
//        $subject->setFilename("bla.jpg");
//        $subject->setFace("face");
//        $subject->setPicture("face");
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($subject);
//        $em->flush();*/
//
//        $subjects = $this->getDoctrine()
//            ->getRepository('WhosThatIdolBundle:Subject')->findAll();
//
//        $view = $this->view($subjects, 200);
//        return $this->handleView($view);
//    }
}