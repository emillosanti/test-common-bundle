<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass(repositoryClass="SAM\CommonBundle\Repository\InteractionNoteRepository")
 * @ORM\Table(name="interaction_note")
 */
class InteractionNote extends Interaction
{
	public function getTypeOfInteraction()
    {
        return 'Note';
    }
}
