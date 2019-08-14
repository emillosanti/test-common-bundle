<?php

namespace SAM\CommonBundle\Entity\Traits;

use SAM\CommonBundle\Entity\InteractionNote;
use Doctrine\Common\Collections\ArrayCollection;

trait InteractionNoteTrait
{
    protected $interactionNotes;

    public function __construct()
    {
        $this->interactionNotes = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getInteractionNotes()
    {
        return $this->interactionNotes;
    }

    /**
     * @param InteractionNote $note
     *
     * @return $this
     */
    public function addInteractionNote($note)
    {
        $this->interactionNotes[] = $note;

        return $this;
    }

    /**
     * @param InteractionNote $note
     *
     * @return $this
     */
    public function removeInteractionNote($note)
    {
        $this->interactionNotes->removeElement($note);

        return $this;
    }
}
