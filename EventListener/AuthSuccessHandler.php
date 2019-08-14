<?php

namespace SAM\CommonBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AuthSuccessHandler
 */
class AuthSuccessHandler implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * AuthSuccessHandler constructor
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
            FOSUserEvents::RESETTING_RESET_COMPLETED => 'onResettingCompleted',
        ];
    }

    /**
     * @param FilterUserResponseEvent $event
     */
    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $this->setResponse($event);
    }

    /**
     * @param FilterUserResponseEvent $event
     */
    public function onResettingCompleted(FilterUserResponseEvent $event)
    {
        $this->setResponse($event);
    }

    /**
     * @param FilterUserResponseEvent $event
     */
    private function setResponse(FilterUserResponseEvent $event)
    {
        if ($event->getResponse() instanceof RedirectResponse) {
            $event->getResponse()->setTargetUrl($this->router->generate('edit_user_account'));
        }
    }
}
