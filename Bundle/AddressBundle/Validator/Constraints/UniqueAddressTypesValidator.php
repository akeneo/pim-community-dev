<?php

namespace Oro\Bundle\AddressBundle\Validator\Constraints;

use Oro\Bundle\AddressBundle\Entity\TypedAddress;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ValidatorException;

class UniqueAddressTypesValidator extends ConstraintValidator
{
    const TYPED_ADDRESS_CLASS = 'Oro\Bundle\AddressBundle\Entity\TypedAddress';

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_array($value) && !($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($value, 'array or Traversable and ArrayAccess');
        }

        $allTypeNames = array();
        $repeatedTypeNames = array();

        /** @var TypedAddress $address */
        foreach ($value as $address) {
            if (!$address instanceof TypedAddress) {
                throw new ValidatorException(
                    sprintf(
                        'Expected element of type %s, %s given',
                        self::TYPED_ADDRESS_CLASS,
                        is_object($value) ? get_class($address) : gettype($address)
                    )
                );
            }

            if ($address->isEmpty()) {
                continue;
            }

            $typeNames = $address->getTypeNames();
            $repeatedTypeNames = array_merge($repeatedTypeNames, array_intersect($allTypeNames, $typeNames));
            $allTypeNames = array_merge($allTypeNames, $typeNames);
        }

        if ($repeatedTypeNames) {
            /** @var UniqueAddressTypes $constraint */
            $this->context->addViolation($constraint->message);
        }
    }
}
