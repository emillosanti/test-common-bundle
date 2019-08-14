<?php

namespace SAM\CommonBundle\Entity\Interfaces;

use SAM\CommonBundle\Entity\InteractionEmail;
use Doctrine\Common\Collections\ArrayCollection;

interface InteractionEmailInterface
{
    /**
     * @return ArrayCollection
     */
    public function getInteractionEmails();

    /**
     * @param InteractionEmail $email
     *
     * @return $this
     */
    public function addInteractionEmail($email);

    /**
     * @param InteractionEmail $email
     *
     * @return $this
     */
    public function removeInteractionEmail($email);
}
