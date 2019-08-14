<?php

namespace SAM\CommonBundle\Twig;

/**
 * Class InteractionExtension
 */
class InteractionExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('get_interactions', [$this, 'getInteractions']),
            new \Twig_SimpleFilter('get_last_note_interaction', [$this, 'getLastNoteInteraction']),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [];
    }

    /**
     * @param object $entity
     *
     * @return array
     */
    public function getInteractions($entity)
    {
        $buffer = array_merge(
            method_exists($entity, 'getInteractionNotes') ? $entity->getInteractionNotes()->toArray() : [],
            method_exists($entity, 'getInteractionEmails') ? $entity->getInteractionEmails()->toArray() : [],
            method_exists($entity, 'getInteractionCalls') ? $entity->getInteractionCalls()->toArray() : [],
            method_exists($entity, 'getInteractionLetters') ? $entity->getInteractionLetters()->toArray() : [],
            method_exists($entity, 'getInteractionAppointments') ? $entity->getInteractionAppointments()->toArray() : []
        );

        usort($buffer, function ($entityInteractionA, $entityInteractionB) {
            if ($entityInteractionA->getEventDate()->getTimestamp() === $entityInteractionB->getEventDate()->getTimestamp()) {
                return 0;
            }

            return ($entityInteractionA->getEventDate()->getTimestamp() > $entityInteractionB->getEventDate()->getTimestamp()) ? -1 : 1;
        });

        return $buffer;
    }

    /*
     * @param  object $entity
     * @return InteractionNote|null
     */
    public function getLastNoteInteraction($entity)
    {
        if (method_exists($entity, 'getInteractionNotes')) {
            return $entity->getInteractionNotes()->last();
        }

        return null;
    }
}
