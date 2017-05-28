<?php

namespace WhosThatIdolBundle\Service;


use Doctrine\ORM\EntityManager;

class Queue
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function queueItemsLeft() {
        return $this->em->getRepository('WhosThatIdolBundle:Subject')->countAll();
    }
}