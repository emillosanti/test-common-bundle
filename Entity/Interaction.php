<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SAM\CommonBundle\Entity\Traits\IdTrait;

abstract class Interaction
{
    use IdTrait;

    const INTERACTION_TYPE_CALL = 'call';
    const INTERACTION_TYPE_APPOINTMENT = 'appointment';
    const INTERACTION_TYPE_EMAIL = 'email';
    const INTERACTION_TYPE_LETTER = 'letter';
    const INTERACTION_TYPE_NOTE= 'note';
    
    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    protected $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="eventDate", type="datetime")
     */
    protected $eventDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reminderDate", type="datetime", nullable=true)
     */
    protected $reminderDate;

    protected $user;

    public function __construct()
    {
        $this->eventDate = new \DateTime();
    }

    abstract public function getTypeOfInteraction();

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @param \DateTime $reminderDate
     */
    public function setReminderDate($reminderDate)
    {
        $this->reminderDate = $reminderDate;
    }

    /**
     * @return \DateTime
     */
    public function getReminderDate()
    {
        return $this->reminderDate;
    }

    /**
     * @param \DateTime $eventDate
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}