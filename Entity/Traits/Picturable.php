<?php

namespace SAM\CommonBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Trait Picturable
 */
trait Picturable
{
    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", nullable=true)
     */
    protected $picture;

    /**
     * @Vich\UploadableField(mapping="contact", fileNameProperty="picture", size="pictureSize")
     *
     * @var File
     */
    protected $pictureFile;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    protected $pictureSize;

    /**
     * @var string
     *
     * @ORM\Column(name="picture_alternative", type="string", nullable=true)
     */
    protected $pictureAlternative;

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     *
     * @return $this
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @param File|null $picture
     *
     * @return $this
     */
    public function setPictureFile(File $picture = null)
    {
        $this->pictureFile = $picture;
        if (null !== $picture) {
            $this->setPictureSize($picture->getSize());
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * @return int
     */
    public function getPictureSize()
    {
        return $this->pictureSize;
    }

    /**
     * @param int $pictureSize
     *
     * @return $this
     */
    public function setPictureSize($pictureSize)
    {
        $this->pictureSize = $pictureSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getPictureAlternative()
    {
        return $this->pictureAlternative;
    }

    /**
     * @param string $pictureAlternative
     *
     * @return self
     */
    public function setPictureAlternative($pictureAlternative)
    {
        $this->pictureAlternative = $pictureAlternative;

        return $this;
    }
}
