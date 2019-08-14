<?php

namespace SAM\CommonBundle\Entity\Interfaces;

use SAM\CommonBundle\Entity\InteractionCall;
use Doctrine\Common\Collections\ArrayCollection;

interface InteractionCallInterface
{
    /**
     * @return ArrayCollection
     */
    public function getInteractionCalls();

    /**
     * @param InteractionCall $call
     *
     * @return $this
     */
    public function addInteractionCall($call);

    /**
     * @param InteractionCall $call
     *
     * @return $this
     */
    public function removeInteractionCall($call);
}
