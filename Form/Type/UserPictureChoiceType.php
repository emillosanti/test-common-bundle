<?php

namespace SAM\CommonBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserPictureChoiceType
 */
class UserPictureChoiceType extends AbstractType
{
	protected $entities;

    public function __construct($entities)
    {
        $this->entities = $entities;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('class', $this->entities['user']['class']);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
