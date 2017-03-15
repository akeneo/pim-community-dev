<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator to check if one of the specified properties is not set but, null or blank.
 *
 * @author    Fabien Lemoine <fabien.lemoine@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotNullPropertiesValidator extends ConstraintValidator
{
    /** @var array */
    protected $supportedTypes;

    /**
     * @param array $supportedTypes
     */
    public function __construct(array $supportedTypes)
    {
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint)
    {
        if (!in_array($attribute->getType(), $this->supportedTypes)) {
            return;
        }

        $values = $attribute->getProperties();

        foreach ($constraint->properties as $propertyCode) {
            if (!array_key_exists($propertyCode, $values) ||
                null === $values[$propertyCode] ||
                '' === $values[$propertyCode]
            ) {
                $this->context->buildViolation($constraint->message)
                    ->atPath($propertyCode)
                    ->addViolation();
            }
        }
    }
}
