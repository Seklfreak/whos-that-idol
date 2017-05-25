<?php

namespace WhosThatIdolBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class TrialUpload
{
    /**
     * @Assert\NotBlank(message="Please, upload the image you want to check..")
     * @Assert\File(mimeTypes={ "image/jpeg", "image/png" })
     */
    private $idolPicture;

    public function getIdolPicture()
    {
        return $this->idolPicture;
    }

    public function setIdolPicture($idolPicture)
    {
        $this->idolPicture = $idolPicture;

        return $this;
    }
}