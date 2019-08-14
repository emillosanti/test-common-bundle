<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass(repositoryClass="SAM\CommonBundle\Repository\InteractionAppointmentRepository")
 * @ORM\Table(name="interaction_appointment")
 */
class InteractionAppointment extends Interaction
{
    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", nullable=true)
     */
    protected $subject;

    /**
     * @var ArrayCollection
     */
    protected $contacts;

    public function getTypeOfInteraction()
    {
        return 'RDV';
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param ContactMerged $contact
     *
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contacts[] = $contact;

        return $this;
    }

    /**
     * @param ContactMerged $contact
     *
     * @return $this
     */
    public function removeContact($contact)
    {
        $this->contacts->removeElement($contact);

        return $this;
    }
}