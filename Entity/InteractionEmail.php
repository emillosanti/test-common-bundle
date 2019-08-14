<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass(repositoryClass="SAM\CommonBundle\Repository\InteractionEmailRepository")
 * @ORM\Table(name="interaction_email")
 */
class InteractionEmail extends Interaction
{
	public function getTypeOfInteraction()
    {
        return 'Email';
    }
}