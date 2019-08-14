<?php

namespace SAM\CommonBundle\Form\Type;

use SAM\CommonBundle\Entity\InteractionEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InteractionEmailType
 */
class InteractionEmailType extends AbstractType
{
    protected $entities;

    public function __construct($entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return InteractionType::class;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', $this->entities['interaction_email']['class']);
    }
}
