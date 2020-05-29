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

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ProductSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ProductSourceOptions;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class ProductSourceOptionsValidator extends ConstraintValidator
{
    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    public function validate($productSource, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, ProductSourceOptions::class);
        Assert::isInstanceOf($productSource, ProductSource::class);

        if (null === $productSource->field || !is_string($productSource->field)) {
            return;
        }
        $attribute = $this->getAttributes->forCode($productSource->field);
        if (null === $attribute) {
            return;
        }

        if (null !== $productSource->format && AttributeTypes::DATE !== $attribute->type()) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ key }}' => 'format',
                    '{{ attribute }}' => $attribute->code(),
                ]
            )->atPath('format')->addViolation();
        }

        if (null !== $productSource->currency && AttributeTypes::PRICE_COLLECTION !== $attribute->type()) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ key }}' => 'currency',
                    '{{ attribute }}' => $attribute->code(),
                ]
            )->atPath('currency')->addViolation();
        }

        if (null !== $productSource->labelLocale && !in_array($attribute->type(), [
            AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT,
            AttributeTypes::REFERENCE_ENTITY_COLLECTION,
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::OPTION_MULTI_SELECT,
        ])) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ key }}' => 'label_locale',
                    '{{ attribute }}' => $attribute->code(),
                ]
            )->atPath('labelLocale')->addViolation();
        }
    }
}
