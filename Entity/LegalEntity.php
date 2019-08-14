<?php

namespace SAM\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Evence\Bundle\SoftDeleteableExtensionBundle\Mapping\Annotation as Evence;

/**
 * @ORM\MappedSuperclass(repositoryClass="SAM\CommonBundle\Repository\LegalEntityRepository")
 * @ORM\Table(name="legal_entity")
 */
class LegalEntity
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_investment_vehicule", type="boolean")
     */
    protected $investmentVehicule = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_parent_company", type="boolean")
     */
    protected $parentCompany = false;

    /**
     * @var int
     *
     * @ORM\Column(name="funds_raised", type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $fundsRaised;

    /**
     * @Evence\onSoftDelete(type="CASCADE")
     * 
     * @var LegalEntity
     */
    protected $parent;

    protected $children;

    protected $categories;

    protected $contactsMerged;

    /**
     * LegalEntity constructor.
     */
    public function __construct()
    {
        $this->investmentVehicule = false;
        $this->parentCompany = false;
        $this->children = new ArrayCollection();
        $this->contactsMerged = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Contact $contact
     *
     * @return $this
     */
    public function addChildren($child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @param LegalEntity $child
     *
     * @return $this
     */
    public function removeChildren($child)
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * @return int
     */
    public function getFundsRaised()
    {
        return $this->fundsRaised;
    }

    /**
     * @param int $fundsRaised
     *
     * @return $this
     */
    public function setFundsRaised($fundsRaised)
    {
        $this->fundsRaised = $fundsRaised;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInvestmentVehicule()
    {
        return $this->investmentVehicule;
    }

    /**
     * @param bool $investmentVehicule
     *
     * @return $this
     */
    public function setInvestmentVehicule($investmentVehicule)
    {
        $this->investmentVehicule = $investmentVehicule;

        return $this;
    }

    /**
     * @return bool
     */
    public function isParentCompany()
    {
        return $this->parentCompany;
    }

    /**
     * @param bool $parentCompany
     *
     * @return $this
     */
    public function setParentCompany($parentCompany)
    {
        $this->parentCompany = $parentCompany;

        return $this;
    }

    /**
     * Add category.
     *
     * @param $category
     *
     * @return LegalEntity
     */
    public function addCategory($category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category.
     *
     * @param $category
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCategory($category)
    {
        return $this->categories->removeElement($category);
    }

    /**
     * Get categories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return Collection
     */
    public function getContactsMerged()
    {
        return $this->contactsMerged;
    }

    /**
     * @param $contactMerged
     *
     * @return $this
     */
    public function addContactMerged($contactMerged)
    {
        $this->contactsMerged->add($contactMerged);

        return $this;
    }

    /**
     * @param $contactMerged
     *
     * @return $this
     */
    public function removeContactMerged($contactMerged)
    {
        $this->contactsMerged->removeElement($contactMerged);

        return $this;
    }
}
