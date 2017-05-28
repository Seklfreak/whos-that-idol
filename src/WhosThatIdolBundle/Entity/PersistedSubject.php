<?php

namespace WhosThatIdolBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PersistedSubject
 *
 * @ORM\Table(name="persisted_subject")
 * @ORM\Entity(repositoryClass="WhosThatIdolBundle\Repository\PersistedSubjectRepository")
 */
class PersistedSubject
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var array
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="Groups", type="array")
     */
    private $groups;

    /**
     * @var string
     *
     * @ORM\Column(name="EnglishName", type="string", length=255)
     */
    private $englishName;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set englishName
     *
     * @param string $englishName
     *
     * @return PersistedSubject
     */
    public function setEnglishName($englishName)
    {
        $this->englishName = $englishName;

        return $this;
    }

    /**
     * Get englishName
     *
     * @return string
     */
    public function getEnglishName()
    {
        return $this->englishName;
    }

    /**
     * Set groups
     *
     * @param array $groups
     *
     * @return Subject
     */
    public function setGroups($groups)
    {
        asort($groups);
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }
}

