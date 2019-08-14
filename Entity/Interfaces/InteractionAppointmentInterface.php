<?php

namespace SAM\CommonBundle\Entity\Interfaces;

use SAM\CommonBundle\Entity\InteractionAppointment;
use Doctrine\Common\Collections\ArrayCollection;

interface InteractionAppointmentInterface
{
    /**
     * @return ArrayCollection
     */
    public function getInteractionAppointments();

    /**
     * @param InteractionAppointment $appointment
     *
     * @return $this
     */
    public function addInteractionAppointment($appointment);

    /**
     * @param InteractionAppointment $appointment
     *
     * @return $this
     */
    public function removeInteractionAppointment($appointment);
}
