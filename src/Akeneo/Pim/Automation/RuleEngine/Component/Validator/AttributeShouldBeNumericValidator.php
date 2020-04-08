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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeShouldBeNumeric;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class AttributeShouldBeNumericValidator extends ConstraintValidator
{
    private const ALLOWED_ATTRIBUTE_TYPES = [
        AttributeTypes::NUMBER,
        AttributeTypes::PRICE_COLLECTION,
    ];

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, AttributeShouldBeNumeric::class);
        if (null === $value) {
            return;
        }

        $attribute = $this->getAttributes->forCode($value);
        if (null === $attribute) {
            // not the responsibility of this validator
            return;
        }

        // TODO RUL-59: Allow metrics
        if (!in_array($attribute->type(), self::ALLOWED_ATTRIBUTE_TYPES)) {
            $this->context->buildViolation(
                $constraint->message,
                ['%attribute_code%' => $attribute->code()]
            )->addViolation();
        }
    }
}
