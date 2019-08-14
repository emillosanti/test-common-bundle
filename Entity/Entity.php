<?php

namespace SAM\CommonBundle\Entity;

class Entity
{
    public function __call($method, $arguments)
    {
        $property = lcfirst(substr($method, 9));

        if (strpos($method, 'increment') === 0)
            $this->$property++;
        else if (strpos($method, 'decrement') === 0)
            $this->$property = max($this->$property - 1, 0);
        else
            throw new \BadMethodCallException('The method '. $method .' does not exist in class '. __CLASS__ .'.');

        return $this;
    }
}