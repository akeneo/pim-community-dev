<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints\AssociationType;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ShouldNotBeTwoWayAndQuantifiedValidator extends ConstraintValidator
{
    public function validate($associationType, Constraint $constraint)
    {
        if (!$constraint instanceof ShouldNotBeTwoWayAndQuantified) {
            throw new UnexpectedTypeException($constraint, ShouldNotBeTwoWayAndQuantified::class);
        }

        if (!$associationType instanceof AssociationTypeInterface) {
            throw new UnexpectedTypeException($associationType, AssociationTypeInterface::class);
        }

        if ($associationType->isQuantified() && $associationType->isTwoWay()) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
