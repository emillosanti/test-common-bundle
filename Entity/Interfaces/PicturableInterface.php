<?php

namespace SAM\CommonBundle\Entity\Interfaces;

use Symfony\Component\HttpFoundation\File\File;

interface PicturableInterface
{
    /**
     * @return string
     */
    public function getPicture();

    /**
     * @param string $picture
     *
     * @return $this
     */
    public function setPicture($picture);

    /**
     * @param File|null $picture
     *
     * @return $this
     */
    public function setPictureFile(File $picture = null);

    /**
     * @return File|null
     */
    public function getPictureFile();

    /**
     * @return int
     */
    public function getPictureSize();

    /**
     * @param int $pictureSize
     *
     * @return $this
     */
    public function setPictureSize($pictureSize);
}
