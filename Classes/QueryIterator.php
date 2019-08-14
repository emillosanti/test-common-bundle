<?php

namespace SAM\CommonBundle\Classes;

use Doctrine\ORM\Query;

/**
 * Class QueryIterator
 * @package SAM\CommonBundle\Classes
 */
class QueryIterator implements \Iterator
{
    /**
     * @var Query
     */
    private $query;

    /**
     * @var
     */
    private $chunkLength;


    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var
     */
    private $offsetDifference = 0;

    /**
     * QueryIterator constructor.
     * @param $query
     * @param $chunkLength
     */
    public function __construct($query, $chunkLength = 100)
    {
        $this->query = $query;
        $this->chunkLength = $chunkLength;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->getData()[$this->index];
    }

    /**
     *
     */
    public function next()
    {
        $this->index ++;
        if ($this->index == $this->chunkLength) {
            $this->offset += $this->chunkLength;
            $this->index = 0;
            $this->data = [];
        }
    }

    /**
     *
     */
    public function key()
    {
        return $this->offset + $this->index;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return array_key_exists($this->index, $this->getData());
    }

    /**
     *
     */
    public function rewind()
    {
        $this->offset = 0;
        $this->index = 0;
        $this->offsetDifference = 0;
    }

    /**
     * @return mixed
     */
    private function getData()
    {
        if (!$this->data) {
            $this->data = $this->query->setFirstResult($this->offset - $this->offsetDifference)->setMaxResults($this->chunkLength)->getResult();
        }
        return $this->data;
    }

    /**
     * @param int $count
     */
    public function decrementOffset($count = 1)
    {
        $this->offsetDifference += $count;
    }
}