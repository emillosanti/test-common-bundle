<?php

namespace SAM\CommonBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This class adds automatically the ManyToOne and OneToMany relations,
 * because it's normally impossible to do so in a mapped superclass.
 */
class DoctrineMappingListener implements EventSubscriber
{
    /**
     * @var string
     */
    private $notificationClass;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @var string
     */
    private $userReadContactMergedClass;

    /**
     * @var string
     */
    private $contactClass;

    /**
     * @var string
     */
    private $companyClass;

    /**
     * @var string
     */
    private $contactMergedClass;

    /**
     * @var string
     */
    private $interactionAppointmentClass;

    /**
     * @var string
     */
    private $interactionEmailClass;

    /**
     * @var string
     */
    private $interactionLetterClass;

    /**
     * @var string
     */
    private $interactionNoteClass;

    /**
     * @var string
     */
    private $interactionCallClass;

    /**
     * @var string
     */
    private $legalEntityClass;

    /**
     * @var string
     */
    private $sourcingClass;

    /**
     * @var string
     */
    private $investorLegalEntityClass;

    /**
     * @var string
     */
    private $sourcingCategoryClass;

    /**
     * @var string
     */
    private $dealFlowClass;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var string
     */
    private $documentCategoryClass;

    /**
     * @var string
     */
    private $categoryClass;

    public function __construct($entities)
    {
        $this->notificationClass = $entities['notification']['class'];
        $this->userClass = $entities['user']['class'];
        $this->userReadContactMergedClass = $entities['user_read_contact_merged']['class'];
        $this->contactClass = $entities['contact']['class'];
        $this->companyClass = $entities['company']['class'];
        $this->contactMergedClass = $entities['contact_merged']['class'];
        $this->interactionAppointmentClass = $entities['interaction_appointment']['class'];
        $this->interactionEmailClass = $entities['interaction_email']['class'];
        $this->interactionLetterClass = $entities['interaction_letter']['class'];
        $this->interactionNoteClass = $entities['interaction_note']['class'];
        $this->interactionCallClass = $entities['interaction_call']['class'];
        $this->legalEntityClass = $entities['legal_entity']['class'];
        $this->sourcingClass = $entities['sourcing']['class'];
        $this->investorLegalEntityClass = $entities['investor_legal_entity']['class'];
        $this->sourcingCategoryClass = $entities['sourcing_category']['class'];
        $this->dealFlowClass = $entities['deal_flow']['class'];
        $this->documentClass = $entities['document']['class'];
        $this->documentCategoryClass = $entities['document_category']['class'];
        $this->categoryClass = $entities['category']['class'];
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();

        $isNotification = is_a($classMetadata->getName(), $this->notificationClass, true);
        $isUser = is_a($classMetadata->getName(), $this->userClass, true);
        $isInteractionAppointment = is_a($classMetadata->getName(), $this->interactionAppointmentClass, true);
        $isInteractionEmail = is_a($classMetadata->getName(), $this->interactionEmailClass, true);
        $isInteractionLetter = is_a($classMetadata->getName(), $this->interactionLetterClass, true);
        $isInteractionNote = is_a($classMetadata->getName(), $this->interactionNoteClass, true);
        $isInteractionCall = is_a($classMetadata->getName(), $this->interactionCallClass, true);
        $isLegalEntity = is_a($classMetadata->getName(), $this->legalEntityClass, true);
        $isSourcing = is_a($classMetadata->getName(), $this->sourcingClass, true);
        $isDocument = is_a($classMetadata->getName(), $this->documentClass, true);

        if ($isNotification) {
            $this->processNotificationMetadata($classMetadata);
        }

        if ($isUser) {
            $this->processUserMetadata($classMetadata);
        }

        if ($isInteractionAppointment) {
            $this->processInteractionAppointmentMetadata($classMetadata);
        }

        if ($isInteractionAppointment || $isInteractionEmail || $isInteractionLetter || $isInteractionNote || $isInteractionCall) {
            $this->processInteractionMetadata($classMetadata);
        }

        if ($isLegalEntity) {
            $this->processLegalEntityMetadata($classMetadata);
        }

        if ($isSourcing) {
            $this->processSourcingMetadata($classMetadata);
        }

        if ($isDocument) {
            $this->processDocumentMetadata($classMetadata);
        }
    }

    /**
     * Declare mapping for Interaction entities
     * @param ClassMetadata $classMetadata
     */
    private function processInteractionMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('user')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'user',
                'targetEntity' => $this->userClass,
            ]);
        }
    }

    /**
     * Declare mapping for InteractionAppointment entity
     * @param ClassMetadata $classMetadata
     */
    private function processInteractionAppointmentMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('contacts')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'contacts',
                'targetEntity' => $this->contactMergedClass,
                'joinTable' => [
                    'name' => 'interaction_appointment_x_contact_merged',
                    'joinColumns' => [[
                        'name' => 'interaction_appointment_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'contact_merged_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }
    }

    /**
     * Declare mapping for Notification entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processNotificationMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('user')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'user',
                'targetEntity' => $this->companyClass,
                'joinColumns' => [[
                    'name' => 'user_id',
                    'referencedColumnName' => 'id',
                ]]
            ]);
        }
    }

    /**
     * Declare mapping for User entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processUserMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('contacts')) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'contacts',
                'targetEntity' => $this->contactClass,
                'mappedBy'   => 'user',
            ]);
        }

        if (!$classMetadata->hasAssociation('readContactMerged')) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'readContactMerged',
                'targetEntity' => $this->userReadContactMergedClass,
                'mappedBy'   => 'user',
            ]);
        }
    }

    /**
     * Declare mapping for Interaction entities
     * @param ClassMetadata $classMetadata
     */
    private function processLegalEntityMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('parent')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'parent',
                'targetEntity' => $this->legalEntityClass,
                'inversedBy'   => 'children',
            ]);
        }

        if (!$classMetadata->hasAssociation('children')) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'children',
                'targetEntity' => $this->legalEntityClass,
                'mappedBy'   => 'parent',
            ]);
        }

        if (!$classMetadata->hasAssociation('categories')) {
            $classMetadata->mapOneToMany([
                'fieldName'     => 'categories',
                'targetEntity'  => $this->categoryClass,
                'mappedBy'      => 'legalEntity'
            ]);
        }

        if (!$classMetadata->hasAssociation('contactsMerged')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'contactsMerged',
                'targetEntity' => $this->contactMergedClass,
                'mappedBy'   => 'legalEntities',
            ]);
        }
    }

    private function processSourcingMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('dealFlow')) {
            $classMetadata->mapOneToOne([
                'fieldName'    => 'dealFlow',
                'targetEntity' => $this->dealFlowClass,
                'inversedBy'   => 'sourcing',
                'joinColumns' => [[
                    'name' => 'deal_flow_id',
                    'referencedColumnName' => 'id',
                ]]
            ]);
        }

        if (!$classMetadata->hasAssociation('investorLegalEntity')) {
            $classMetadata->mapOneToOne([
                'fieldName'    => 'investorLegalEntity',
                'targetEntity' => $this->investorLegalEntityClass,
                'inversedBy'   => 'sourcing',
                'joinColumns' => [[
                    'name' => 'investor_legal_entity_id',
                    'referencedColumnName' => 'id',
                ]]
            ]);
        }

        if (!$classMetadata->hasAssociation('category')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'category',
                'targetEntity' => $this->sourcingCategoryClass,
            ]);
        }

        if (!$classMetadata->hasAssociation('company')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'company',
                'targetEntity' => $this->companyClass,
                'cascade' => [ 'persist' ],
            ]);
        }

        if (!$classMetadata->hasAssociation('contact')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'contact',
                'targetEntity' => $this->contactMergedClass,
            ]);
        }
    }

    private function processDocumentMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('category')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'category',
                'targetEntity' => $this->documentCategoryClass,
            ]);
        }

        if (!$classMetadata->hasAssociation('author')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'author',
                'targetEntity' => $this->userClass,
            ]);
        }
    }
}
