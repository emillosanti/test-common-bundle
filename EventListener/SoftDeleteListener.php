<?php

namespace SAM\CommonBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class SoftDeleteListener
{
    public function preRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
    
        if (method_exists($entity, 'getSlug')) {
            $entity->setSlug($entity->getSlug() . '-' . (new \DateTime())->format('Y-m-d-H:i:s'));

            $om = $event->getObjectManager();
            $om->persist($entity);
            $om->flush();
        }
    }
}
