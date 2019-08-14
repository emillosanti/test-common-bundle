<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass(repositoryClass="SAM\CommonBundle\Repository\InteractionLetterRepository")
 * @ORM\Table(name="interaction_letter")
 */
class InteractionLetter extends Interaction
{
	public function getTypeOfInteraction()
    {
        return 'Lettre';
    }
}
