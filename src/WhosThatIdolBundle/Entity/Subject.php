<?php

namespace WhosThatIdolBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Subject
 *
 * @ORM\Table(name="subject")
 * @ORM\Entity(repositoryClass="WhosThatIdolBundle\Repository\SubjectRepository")
 */
class Subject
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
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="Name", type="string", length=255)
     */
    private $name;

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
     * @ORM\Column(name="Filename", type="string", length=255, nullable=TRUE)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="Picture", type="text", nullable=TRUE)
     */
    private $picture;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="Face", type="text")
     */
    private $face;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="Source", type="string", length=255)
     */
    private $source;

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source)
    {
        $this->source = $source;
    }


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
     * Set name
     *
     * @param string $name
     *
     * @return Subject
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Subject
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set picture
     *
     * @param string $picture
     *
     * @return Subject
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set face
     *
     * @param string $face
     *
     * @return Subject
     */
    public function setFace($face)
    {
        $this->face = $face;

        return $this;
    }

    /**
     * Get face
     *
     * @return string
     */
    public function getFace()
    {
        return $this->face;
    }
}

