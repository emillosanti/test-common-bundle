<?php

namespace SAM\CommonBundle\Twig;

class InstanceofExtension extends \Twig_Extension
{
    public function getTests()
    {
        return [
            new \Twig_Test('instanceof', [$this, 'isInstanceof'])
        ];
    }

    /**
     * @param $var
     * @param $instance
     * @return bool
     */
    public function isInstanceof($var, $instance) {
        return $var instanceof $instance;
    }
}