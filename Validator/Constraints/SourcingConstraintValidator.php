<?php

namespace SAM\CommonBundle\Validator\Constraints;

use SAM\CommonBundle\Entity\Sourcing;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SourcingConstraintValidator
 */
class SourcingConstraintValidator extends ConstraintValidator
{
    /**
     * @param Sourcing   $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof Sourcing) {
            if (!$value->getContact() && !$value->getCompany()) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
