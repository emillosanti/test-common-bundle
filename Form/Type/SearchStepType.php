<?php

namespace SAM\CommonBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchStepType
 */
class SearchStepType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('placeholder', 'Tous');

        $placeholderNormalizer = function (Options $options, $placeholder) {
            // empty value has been set explicitly
            return $placeholder;
        };

        $resolver->setNormalizer('placeholder', $placeholderNormalizer);
    }
}
