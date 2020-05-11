<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\NotBlankCurrency;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class NotBlankCurrencyValidator extends ConstraintValidator
{
    /** @var GetAttributes */
    private $getAttributes;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    public function __construct(GetAttributes $getAttributes, PropertyAccessorInterface $propertyAccessor)
    {
        $this->getAttributes = $getAttributes;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function validate($object, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, NotBlankCurrency::class);

        $currencyCode = $this->propertyAccessor->getValue($object, $constraint->currencyProperty);
        if (null !== $currencyCode && '' !== $currencyCode) {
            return;
        }

        $attributeCode = $this->propertyAccessor->getValue($object, $constraint->attributeProperty);
        if (null === $attributeCode || !is_string($attributeCode)) {
            return;
        }
        $attribute = $this->getAttributes->forCode($attributeCode);
        if (null === $attribute || AttributeTypes::PRICE_COLLECTION !== $attribute->type()) {
            return;
        }

        $this->context->buildViolation(
            $constraint->message,
            ['{{ currencyProperty }}' => $constraint->currencyProperty]
        )->addViolation();
    }
}
