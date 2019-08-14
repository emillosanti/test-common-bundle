<?php

namespace SAM\CommonBundle\Entity\Traits;

use SAM\CommonBundle\Entity\InteractionAppointment;
use Doctrine\Common\Collections\ArrayCollection;

trait InteractionAppointmentTrait
{
    protected $interactionAppointments;

    public function __construct()
    {
        $this->interactionAppointments = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getInteractionAppointments()
    {
        return $this->interactionAppointments;
    }

    /**
     * @param InteractionAppointment $appointment
     *
     * @return $this
     */
    public function addInteractionAppointment($appointment)
    {
        $this->interactionAppointments[] = $appointment;

        return $this;
    }

    /**
     * @param InteractionAppointment $appointment
     *
     * @return $this
     */
    public function removeInteractionAppointment($appointment)
    {
        $this->interactionAppointments->removeElement($appointment);

        return $this;
    }
}
