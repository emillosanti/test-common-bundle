<?php

namespace SAM\CommonBundle\EventListener;

use FOS\UserBundle\Model\UserInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * class UserListener.
 */
class UserListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof UserInterface) {
            return;
        }

        if (null === $object->getCode()) {
            $trigram = sprintf(
                '%s%s%s',
                substr($object->getFirstName(), 0, 1),
                substr($object->getLastName(), 0, 1),
                substr($object->getLastName(), strlen($object->getLastName()) - 1, 1)
            );
            $object->setCode(strtoupper($trigram));
        }
    }
}
