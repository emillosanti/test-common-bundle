<?php

namespace SAM\CommonBundle\EventListener;

use SAM\CommonBundle\Entity\Interaction;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * class InteractionListener.
 */
class InteractionListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * DealFlowListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Interaction) {
            return;
        }
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            $object->setUser($token->getUser());
        }

        if (!$object->getEventDate()) {
            $object->setEventDate(new \DateTime());
        }
    }
}
