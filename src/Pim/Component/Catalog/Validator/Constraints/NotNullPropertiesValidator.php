<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator to check if one of the specified properties is set but is null.
 *
 * @author    Fabien Lemoine <fabien.lemoine@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotNullPropertiesValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $values = $value->getProperties();

        foreach ($constraint->properties as $propertyCode) {
            if (array_key_exists($propertyCode, $values) && null === $values[$propertyCode]) {
                $this->context->buildViolation($constraint->message)
                    ->atPath($constraint->propertyPath)
                    ->addViolation();
            }
        }
    }
}
