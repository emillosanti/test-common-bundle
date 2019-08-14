<?php

namespace SAM\CommonBundle\Entity\Traits;

use SAM\CommonBundle\Entity\InteractionCall;
use Doctrine\Common\Collections\ArrayCollection;

trait InteractionCallTrait
{
    protected $interactionCalls;

    public function __construct()
    {
        $this->interactionCalls = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getInteractionCalls()
    {
        return $this->interactionCalls;
    }

    /**
     * @param InteractionCall $call
     *
     * @return $this
     */
    public function addInteractionCall($call)
    {
        $this->interactionCalls[] = $call;

        return $this;
    }

    /**
     * @param InteractionCall $call
     *
     * @return $this
     */
    public function removeInteractionCall($call)
    {
        $this->interactionCalls->removeElement($call);

        return $this;
    }
}
