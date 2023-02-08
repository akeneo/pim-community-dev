<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeShouldHaveTypesValidator extends ConstraintValidator
{
    public function __construct(private GetAttributes $getAttributes)
    {
    }

    public function validate($target, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, AttributeShouldHaveTypes::class);
        if (!\is_string($target)) {
            return;
        }

        $attribute = $this->getAttributes->forCode($target);
        if (null !== $attribute && !\in_array($attribute->type(), $constraint->types)) {
            $this->context
                ->buildViolation($constraint->message, [
                    '{{ code }}' => $target,
                    '{{ type }}' => $attribute->type(),
                    '{{ expected }}' => \implode(', ', $constraint->types),
                ])
                ->addViolation();
        }
    }
}
