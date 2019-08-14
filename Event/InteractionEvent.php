<?php

namespace SAM\CommonBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class InteractionEvent extends Event
{
	protected $interaction;

	protected $entity;

	protected $interactionType;

	public function __construct($interaction, $interactionType, $entity)
    {
        $this->interaction = $interaction;
        $this->entity = $entity;
        $this->interactionType = $interactionType;
    }

    public function getInteraction()
    {
        return $this->interaction;
    }

    public function getEntity()
    {
    	return $this->entity;
    }

    public function getInteractionType()
    {
    	return $this->interactionType;
    }
}