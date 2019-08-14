<?php

namespace SAM\CommonBundle\Twig;

use SAM\CommonBundle\Manager\PictureManager;

/**
 * Class PictureExtension
 */
class PictureExtension extends \Twig_Extension
{
    /**
     * @var PictureManager
     */
    private $pictureManager;

    /**
     * PictureExtension constructor.
     *
     * @param PictureManager $pictureManager
     */
    public function __construct(PictureManager $pictureManager)
    {
        $this->pictureManager = $pictureManager;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('picture', [$this, 'getPicture']),
        ];
    }

    /**
     * @param object $object
     * @param array  $params
     *
     * @return string
     */
    public function getPicture($object, $params = [])
    {
        return $this->pictureManager->getPicture($object, $params);
    }
}
