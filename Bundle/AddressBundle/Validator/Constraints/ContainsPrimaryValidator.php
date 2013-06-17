<?php

namespace Oro\Bundle\AddressBundle\Validator\Constraints;

use Oro\Bundle\AddressBundle\Entity\TypedAddress;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContainsPrimaryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!is_array($value) && !($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($value, 'array or Traversable and ArrayAccess');
        }

        $count = 0;
        /** @var TypedAddress $item */
        foreach ($value as $item) {
            if ($item instanceof TypedAddress && $item->isPrimary()) {
                $count++;
            }
        }

        if (count($value) > 0 && $count != 1) {
            $this->context->addViolation($constraint->message);
        }
    }
}
