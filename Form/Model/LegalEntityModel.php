<?php

namespace SAM\CommonBundle\Form\Model;

final class LegalEntityModel
{
    /** @var LegalEntity */
    protected $legalEntity;

    public function __construct($legalEntity = null)
    {
        $this->legalEntity = $legalEntity;
    }

    /**
     * @return \DateTime
     */
    public function getLegalEntity()
    {
        return $this->legalEntity;
    }

    /**
     * @param LegalEntity $legalEntity
     * @return LegalEntity
     */
    public function setLegalEntity($legalEntity)
    {
        
        $this->legalEntity = $legalEntity;
        return $this;
    }
}