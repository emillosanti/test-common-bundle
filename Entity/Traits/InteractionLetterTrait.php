<?php

namespace SAM\CommonBundle\Entity\Traits;

use SAM\CommonBundle\Entity\InteractionLetter;
use Doctrine\Common\Collections\ArrayCollection;

trait InteractionLetterTrait
{
    protected $interactionLetters;

    public function __construct()
    {
        $this->interactionLetters = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getInteractionLetters()
    {
        return $this->interactionLetters;
    }

    /**
     * @param InteractionLetter $letter
     *
     * @return $this
     */
    public function addInteractionLetter($letter)
    {
        $this->interactionLetters[] = $letter;

        return $this;
    }

    /**
     * @param InteractionLetter $letter
     *
     * @return $this
     */
    public function removeInteractionLetter($letter)
    {
        $this->interactionLetters->removeElement($letter);

        return $this;
    }
}
