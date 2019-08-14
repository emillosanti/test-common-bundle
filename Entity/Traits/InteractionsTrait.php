<?php

namespace SAM\CommonBundle\Entity\Traits;

use SAM\CommonBundle\Entity\InteractionCall;
use Doctrine\Common\Collections\ArrayCollection;

trait InteractionsTrait
{
    /**
     * @return ArrayCollection
     */
    public function getLastInteraction()
    {
        $interactions = [];
        if (method_exists($this, 'getInteractionCalls')) {
            foreach ($this->getInteractionCalls() as $interaction) {
                $interactions[] = $interaction;
            }
        }

        if (method_exists($this, 'getInteractionEmails')) {
            foreach ($this->getInteractionEmails() as $interaction) {
                $interactions[] = $interaction;
            }
        }

        if (method_exists($this, 'getInteractionLetters')) {
            foreach ($this->getInteractionLetters() as $interaction) {
                $interactions[] = $interaction;
            }
        }

        if (method_exists($this, 'getInteractionNotes')) {
            foreach ($this->getInteractionNotes() as $interaction) {
                $interactions[] = $interaction;
            }
        }

        if (method_exists($this, 'getInteractionAppointments')) {
            foreach ($this->getInteractionAppointments() as $interaction) {
                $interactions[] = $interaction;
            }
        }


        usort($interactions, function($a, $b) {
            return $a->getEventDate() > $b->getEventDate();
        });
        return end($interactions);
    }
}
