<?php

namespace SAM\CommonBundle\Utils;

use Behat\Transliterator\Transliterator;

class Utils
{
    /**
     * @param string $string
     *
     * @return string
     */
    public static function sanitizeSlug($string)
    {
        return Transliterator::urlize($string);
    }
}
