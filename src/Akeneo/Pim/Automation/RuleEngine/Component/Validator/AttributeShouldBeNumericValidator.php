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
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AttributeShouldBeNumericValidator extends ConstraintValidator
{
    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AttributeShouldBeNumeric) {
            throw new UnexpectedTypeException($constraint, AttributeShouldBeNumeric::class);
        }

        if (null === $value) {
            return;
        }

        $attribute = $this->getAttributes->forCode($value);
        if (null === $attribute) {
            throw new \InvalidArgumentException(sprintf('Attribute "%s" does not exist', $value));
        }

        // TODO RUL-59 / RUL-60: Allow metrics and prices
        if (AttributeTypes::NUMBER !== $attribute->type()) {
            $this->context->buildViolation(
                $constraint->message,
                ['%attribute_code%' => $attribute->code()]
            )->addViolation();
        }
    }
}
