<?php

namespace WhosThatIdolBundle\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

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
}