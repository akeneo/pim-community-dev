<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\AttributeTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator to check if properties are not left blank
 *
 * @author    Fabien Lemoine <fabien.lemoine@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotBlankPropertiesValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $properties = $constraint->properties;
        $values = $value->getProperties();
        if (in_array($value->getAttributeType(), [
            AttributeTypes::REFERENCE_DATA_MULTI_SELECT,
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
        ])) {
            foreach ($properties as $propertyCode) {
                if (array_key_exists($propertyCode, $values) && null === $values[$propertyCode]) {
                    $this->context->buildViolation($constraint->message)->addViolation();
                }
            }
        }
    }
}
