<?php

namespace SAM\CommonBundle\Entity\Interfaces;

use SAM\CommonBundle\Entity\InteractionLetter;
use Doctrine\Common\Collections\ArrayCollection;

interface InteractionLetterInterface
{
    /**
     * @return ArrayCollection
     */
    public function getInteractionLetters();

    /**
     * @param InteractionLetter $letter
     *
     * @return $this
     */
    public function addInteractionLetter($letter);

    /**
     * @param InteractionLetter $letter
     *
     * @return $this
     */
    public function removeInteractionLetter($letter);
}
