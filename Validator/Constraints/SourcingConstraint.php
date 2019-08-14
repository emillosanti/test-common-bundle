<?php

namespace SAM\CommonBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SourcingConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Le sourcing doit être composé d\'au moins un contact et/ou une société';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
