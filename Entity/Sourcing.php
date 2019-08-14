<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Evence\Bundle\SoftDeleteableExtensionBundle\Mapping\Annotation as Evence;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="sourcing")
 */
class Sourcing
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var DealFlow
     */
    protected $dealFlow;

    /**
     * @var InvestorLegalEntity
     */
    protected $investorLegalEntity;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var ContactMerged
     * 
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $contact;

    /**
     * @var Company
     * 
     * @Evence\onSoftDelete(type="SET NULL")
     */
    protected $company;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DealFlow|null
     */
    public function getDealFlow()
    {
        return $this->dealFlow;
    }

    /**
     * @param DealFlow $dealFlow
     *
     * @return $this
     */
    public function setDealFlow($dealFlow): Sourcing
    {
        $this->dealFlow = $dealFlow;

        return $this;
    }

    /**
     * @return InvestorLegalEntity|null
     */
    public function getInvestorLegalEntity()
    {
        return $this->investorLegalEntity;
    }

    /**
     * @param InvestorLegalEntity $investorLegalEntity
     * @return Sourcing
     */
    public function setInvestorLegalEntity($investorLegalEntity): Sourcing
    {
        $this->investorLegalEntity = $investorLegalEntity;
        return $this;
    }

    /**
     * @return SourcingCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param SourcingCategory $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return ContactMerged
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param ContactMerged $contact
     *
     * @return $this
     */
    public function setContact($contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param Company $company
     *
     * @return $this
     */
    public function setCompany($company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailsAsString()
    {
        if ($this->getContact()) {
            return $this->getContact()->getEmailsAsString();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPhonesAsString()
    {
        if ($this->getContact()) {
            return $this->getContact()->getPhonesAsString();
        }

        $phones = [];
        if ($this->getCompany() && $this->getCompany()->getPhoneNumber()) {
            $phones[] = $this->getCompany()->getPhoneNumber();
        }

        return implode(' | ', $phones);
    }
}
