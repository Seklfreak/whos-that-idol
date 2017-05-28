<?php

namespace WhosThatIdolBundle\Repository;

use Doctrine\ORM\EntityRepository;
use WhosThatIdolBundle\Entity\PersistedSubject;

class PersistedSubjectRepository extends EntityRepository
{
    public function getByEnglishNameAndGroups(string $englishName, array $groups)
    {
        asort($groups);
        $foundSubjects = $this->findBy(array(
            'englishName' => $englishName
        ));
        if ($foundSubjects != null) {
            foreach ($foundSubjects as $subject) {
                if ($subject->getGroups() === $groups) {
                    return $subject;
                }
            }
        }

        $newSubject = new PersistedSubject();
        $newSubject->setGroups($groups);
        $newSubject->setEnglishName($englishName);

        $this->getEntityManager()->persist($newSubject);
        $this->getEntityManager()->flush();

        return $newSubject;
    }
}
