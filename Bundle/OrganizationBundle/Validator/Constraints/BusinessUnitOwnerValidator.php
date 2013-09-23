<?php

namespace Oro\Bundle\OrganizationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BusinessUnitOwnerValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value->getOwner() && $value->getId() == $value->getOwner()->getId()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
