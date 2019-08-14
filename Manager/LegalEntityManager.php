<?php

namespace SAM\CommonBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;
use SAM\CommonBundle\Entity\LegalEntity;

class LegalEntityManager
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /** @var EntityManagerInterface */
    protected $em;

    const SESSION_KEY_CURRENT_LEGAL_ENTITY = 'current-legal-entity';

    /**
     * SearchManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->session = new Session();
        $this->em = $entityManager;
    }

    /**
     * Get current legal entity from session
     * 
     * @return LegalEntity|null
     */
    public function getCurrentLegalEntity()
    {
        if ($this->session->has(self::SESSION_KEY_CURRENT_LEGAL_ENTITY)) {
            $legalEntityId = $this->session->get(self::SESSION_KEY_CURRENT_LEGAL_ENTITY);
            if (is_int($legalEntityId)) {
                return $this->em->getRepository('legal_entity')->find($legalEntityId);
            }
        }

        return null;
    }

    /**
     * Get current legal entity if it's an investment vehicule
     * 
     * @return LegalEntity|null
     */
    public function getCurrentInvestmentLegalEntity()
    {
        $currentLegalEntity = $this->getCurrentLegalEntity();

        return null !== $currentLegalEntity && $currentLegalEntity->isInvestmentVehicule() ? $currentLegalEntity : null;
    }

    /**
     * Set current legal entity into session
     * 
     * @param LegalEntity $legalEntity
     */
    public function setCurrentLegalEntity($legalEntity)
    {
        $this->session->set(self::SESSION_KEY_CURRENT_LEGAL_ENTITY, $legalEntity->getId());
    }

    public function getQueryBuilderParentLegalEntities()
    {
        return $this->em->getRepository('legal_entity')->findQueryBuilderParent();
    }

    public function getQueryBuilderChildrenLegalEntities()
    {
        return $this->em->getRepository('legal_entity')->findQueryBuilderChildren();
    }
}
