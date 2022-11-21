<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator to check that all specified properties are not set or set to null.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NullPropertiesValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NullProperties) {
            throw new UnexpectedTypeException($constraint, NullProperties::class);
        }

        $values = $value->getProperties();

        foreach ($constraint->properties as $propertyCode) {
            if (array_key_exists($propertyCode, $values) && null !== $values[$propertyCode]) {
                $this->context->buildViolation($constraint->message)
                    ->atPath($propertyCode)
                    ->addViolation();
            }
        }
    }
}
