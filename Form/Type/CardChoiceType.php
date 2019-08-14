<?php

namespace SAM\CommonBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

/**
 * Class CardChoiceType
 */
class CardChoiceType extends AbstractType
{
    /**
     * @return string
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
