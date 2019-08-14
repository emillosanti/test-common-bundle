<?php

namespace SAM\CommonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserCardChoiceType
 */
class UserCardChoiceType extends AbstractType
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
        $resolver->setDefault('class', $this->entities['user']['class']);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return CardChoiceType::class;
    }
}
