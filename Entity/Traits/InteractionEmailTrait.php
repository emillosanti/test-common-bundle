<?php

namespace SAM\CommonBundle\Entity\Traits;

use SAM\CommonBundle\Entity\InteractionEmail;
use Doctrine\Common\Collections\ArrayCollection;

trait InteractionEmailTrait
{
    protected $interactionEmails;

    public function __construct() {
        $this->interactionEmails = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getInteractionEmails()
    {
        return $this->interactionEmails;
    }

    /**
     * @param InteractionEmail $email
     *
     * @return $this
     */
    public function addInteractionEmail($email)
    {
        $this->interactionEmails[] = $email;

        return $this;
    }

    /**
     * @param InteractionEmail $email
     *
     * @return $this
     */
    public function removeInteractionEmail($email)
    {
        $this->interactionEmails->removeElement($email);

        return $this;
    }
}
