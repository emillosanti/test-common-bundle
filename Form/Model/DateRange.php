<?php

namespace SAM\CommonBundle\Form\Model;

final class DateRange
{
    /** @var \DateTime */
    protected $start;

    /** @var \DateTime */
    protected $end;

    /**
     * DateRange constructor.
     * @param \DateTime $start
     * @param \DateTime $end
     */
    public function __construct(\DateTime $start = null, \DateTime $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     * @return DateRange
     */
    public function setStart(\DateTime $start)
    {
        if ($start) {
            $start->setTime(0, 0, 0);
        }
        
        $this->start = $start;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return DateRange
     */
    public function setEnd(\DateTime $end)
    {
        if ($end) {
            $end->setTime(23, 59, 59);
        }

        $this->end = $end;
        return $this;
    }
}