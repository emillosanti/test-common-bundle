<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SAM\CommonBundle\Entity\Traits\IdTrait;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="search_hit")
 */
class SearchHit
{
    use IdTrait;
    use TimestampableEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="string", nullable=true)
     */
    protected $query;

    /**
     * @var string
     *
     * @ORM\Column(name="bundle", type="string", nullable=false)
     */
    protected $bundle;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", nullable=false)
     */
    protected $target;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getQuery();
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     *
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     *
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param string $bundle
     *
     * @return $this
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }
}
