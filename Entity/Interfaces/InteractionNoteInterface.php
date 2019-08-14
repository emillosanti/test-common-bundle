<?php

namespace SAM\CommonBundle\Entity\Interfaces;

use SAM\CommonBundle\Entity\InteractionNote;
use Doctrine\Common\Collections\ArrayCollection;

interface InteractionNoteInterface
{
    /**
     * @return ArrayCollection
     */
    public function getInteractionNotes();

    /**
     * @param InteractionNote $note
     *
     * @return $this
     */
    public function addInteractionNote($note);

    /**
     * @param InteractionNote $note
     *
     * @return $this
     */
    public function removeInteractionNote($note);
}