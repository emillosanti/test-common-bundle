<?php

namespace SAM\CommonBundle\Form\Type;

use SAM\AddressBookBundle\Entity\ContactMerged;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserCardChoiceType
 */
class ContactMergedCardChoiceType extends AbstractType
{
    protected $entities;

    public function __construct($entities)
    {
        $this->entities = $entities;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('class', $this->entities['contact_merged']['class']);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return CardChoiceType::class;
    }
}
