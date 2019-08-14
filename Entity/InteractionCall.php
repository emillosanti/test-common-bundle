<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass(repositoryClass="SAM\CommonBundle\Repository\InteractionCallRepository")
 * @ORM\Table(name="interaction_call")
 */
class InteractionCall extends Interaction
{
    const RESULT_NO_ANSWER = 10;
    const RESULT_NOT_AVAILABLE = 20;
    const RESULT_WRONG_NUMBER = 30;
    const RESULT_MESSAGE_LEFT = 40;
    const RESULT_MESSAGE_SEND = 50;
    const RESULT_DONE = 60;

    /**
     * @var int
     *
     * @ORM\Column(name="result", type="integer")
     */
    protected $result;

    public function getTypeOfInteraction()
    {
        return 'Téléphone';
    }

    /**
     * @return array
     */
    public static function getResultChoices()
    {
        return [
            'Pas de réponse'     => self::RESULT_NO_ANSWER,
            'Occupé'             => self::RESULT_NOT_AVAILABLE,
            'Mauvais numéro'     => self::RESULT_WRONG_NUMBER,
            'Message laissé sur' => self::RESULT_MESSAGE_LEFT,
            'Message transmis'   => self::RESULT_MESSAGE_SEND,
            'Effectué'           => self::RESULT_DONE,
        ];
    }

    /**
     * @return string|null
     */
    public function getResultAsString()
    {
        $choices = array_flip(self::getResultChoices());

        return isset($choices[$this->getResult()]) ? $choices[$this->getResult()] : null;
    }

    /**
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param int $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}