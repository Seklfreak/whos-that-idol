<?php

namespace WhosThatIdolBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SubjectRepository extends EntityRepository
{
    public function countAll()
    {
        $qb = $this->createQueryBuilder('s');
        return $qb
            ->select('count(s.id)')
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 1*60)
            ->getSingleScalarResult();
    }
}
