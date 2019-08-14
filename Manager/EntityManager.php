<?php

namespace SAM\CommonBundle\Manager;

/**
 * Class EntityManager
 */
class EntityManager
{
    /**
     * @var array
     */
    private $entities;

    /**
     * EntityManager constructor.
     *
     * @param array $entities
     */
    public function __construct($entities)
    {
        $this->entities = $entities;
    }

    /**
     * @param string $entity
     *
     * @return string
     */
    public function getNamespace($entity)
    {
        if (!isset($this->entities[$entity])) {
            return null;
        }

        return $this->entities[$entity]['class'];
    }
}
