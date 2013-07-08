<?php

namespace Oro\Bundle\AddressBundle\Validator\Constraints;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ValidatorException;

class UniqueAddressTypesValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_array($value) && !($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($value, 'array or Traversable and ArrayAccess');
        }

        $typeNamesToLabels = array();
        $allTypeNames = array();
        $repeatedTypeNames = array();

        /** @var AbstractTypedAddress $address */
        foreach ($value as $address) {
            if (!$address instanceof AbstractTypedAddress) {
                throw new UnexpectedTypeException($value, 'Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress');
            }

            if ($address->isEmpty()) {
                continue;
            }

            foreach ($address->getTypes() as $type) {
                $typeNamesToLabels[$type->getName()] = $type->getLabel();
            }

            $typeNames = $address->getTypeNames();
            $repeatedTypeNames = array_merge($repeatedTypeNames, array_intersect($allTypeNames, $typeNames));
            $allTypeNames = array_merge($allTypeNames, $typeNames);
        }

        if ($repeatedTypeNames) {
            $repeatedTypeLabels = array();
            foreach ($repeatedTypeNames as $name) {
                $repeatedTypeLabels[] = $typeNamesToLabels[$name];
            }
            /** @var UniqueAddressTypes $constraint */
            $this->context->addViolation(
                $constraint->message,
                array('{{ types }}' => '"' . implode('", "', $repeatedTypeLabels) . '"')
            );
        }
    }
}
