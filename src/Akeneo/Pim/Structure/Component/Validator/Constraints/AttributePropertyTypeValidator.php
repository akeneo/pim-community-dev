<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributePropertyTypeValidator extends ConstraintValidator
{
    public function validate($attribute, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, AttributePropertyType::class);
        Assert::isInstanceOf($attribute, AttributeInterface::class);

        $validator = $this->context->getValidator();
        $typeConstraint = new Type(array_filter([
            'type' => $constraint->type,
            'message' => $constraint->message,
        ]));

        foreach ($constraint->properties as $property) {
            $value = $attribute->getProperty($property);
            if (null !== $value) {
                $violations = $validator->validate($value, $typeConstraint);
                /** @var ConstraintViolationInterface $violation */
                foreach ($violations as $violation) {
                    $this->context->buildViolation($violation->getMessage(), $violation->getParameters())
                                  ->atPath($property)
                                  ->addViolation();
                }
            }
        }
    }
}
