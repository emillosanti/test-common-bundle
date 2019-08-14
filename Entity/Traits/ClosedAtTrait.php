<?php

namespace SAM\CommonBundle\Entity\Traits;

trait ClosedAtTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closed_at", type="datetime", nullable=true)
     * @Gedmo\Versioned
     */
    protected $closedAt;

    /**
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * @param \DateTime $closedAt
     *
     * @return $this
     */
    public function setClosedAt(\DateTime $closedAt = null)
    {
        $this->closedAt = $closedAt;

        return $this;
    }
}