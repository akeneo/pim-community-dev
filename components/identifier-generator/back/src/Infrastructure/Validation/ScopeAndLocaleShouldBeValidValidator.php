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
final class ScopeAndLocaleShouldBeValidValidator extends ConstraintValidator
{
    public function __construct(private GetAttributes $getAttributes)
    {
    }

    public function validate($condition, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ScopeAndLocaleShouldBeValid::class);
        if (!\is_array($condition)) {
            return;
        }

        if (!\array_key_exists('attributeCode', $condition)) {
            return;
        }

        $attribute = $this->getAttributes->forCode($condition['attributeCode']);
        if (null === $attribute) {
            return;
        }

        if ($attribute->isScopable()) {
            if (!\array_key_exists('scope', $condition)) {
                $this->context
                    ->buildViolation($constraint->missingField)
                    ->atPath('[scope]')
                    ->addViolation();
            }
        }
    }
}
