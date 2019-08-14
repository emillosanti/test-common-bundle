<?php

namespace SAM\CommonBundle\EventListener;

use Algolia\SearchBundle\IndexManagerInterface;
use AppBundle\Entity\Company;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Events;

class SearchIndexerSubscriber implements EventSubscriber
{
    /** @var IndexManagerInterface */
    protected $indexManager;

    /** @var boolean */
    protected $enableEnhancedSearch;

    /**
     * SearchIndexerSubscriber constructor.
     * @param IndexManagerInterface $indexManager
     */
    public function __construct($enableEnhancedSearch, IndexManagerInterface $indexManager)
    {
        $this->indexManager = $indexManager;
        $this->enableEnhancedSearch = $enableEnhancedSearch;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        if ($this->enableEnhancedSearch) {
            $this->index($args);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if ($this->enableEnhancedSearch) {
            $this->index($args);
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        if ($this->enableEnhancedSearch) {
            $this->indexManager->remove($object = $args->getObject(), $args->getObjectManager());
        }
    }

    public function index(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Company) {
            $contactsMerged = $entity->getContactsMerged();
            foreach ($contactsMerged as $contactMerged) {
                if ($contactMerged->getId()) {
                    $this->indexManager->index($contactMerged, $args->getObjectManager());
                }
            }

            $contacts = $entity->getContacts();
            foreach ($contacts as $contact) {
                if ($contact->getId()) {
                    $this->indexManager->index($contact, $args->getObjectManager());
                }
            }
        }
        
        $this->indexManager->index($entity, $args->getObjectManager());
    }
}