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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\CurrencyKey;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class CurrencyKeyValidator extends ConstraintValidator
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
        Assert::isInstanceOf($constraint, CurrencyKey::class);

        if (null === $object) {
            return;
        }

        $currencyCode = $this->propertyAccessor->getValue($object, $constraint->currencyProperty);
        $attributeCode = $this->propertyAccessor->getValue($object, $constraint->attributeProperty);
        if (null === $attributeCode || !is_string($attributeCode)) {
            return;
        }
        $attribute = $this->getAttributes->forCode($attributeCode);
        if (null === $attribute) {
            return;
        }

        if (null === $currencyCode || '' === $currencyCode) {
            if (AttributeTypes::PRICE_COLLECTION === $attribute->type()) {
                $this->context
                    ->buildViolation(
                        $constraint->emptyKeyMessage,
                        [
                            '{{ key }}' => $constraint->currencyProperty,
                        ]
                    )->atPath($constraint->currencyProperty)
                    ->setInvalidValue($currencyCode)
                    ->addViolation();
            }

            return;
        }

        if (AttributeTypes::PRICE_COLLECTION !== $attribute->type()) {
            $this->context
                ->buildViolation(
                    $constraint->unexpectedKeyMessage,
                    [
                        '{{ key }}' => $constraint->currencyProperty,
                    ]
                )->atPath($constraint->currencyProperty)
                ->setInvalidValue($currencyCode)
                ->addViolation();
        }
    }
}
