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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class AttributeTypesValidator extends ConstraintValidator
{
    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, AttributeTypes::class);

        if (!is_string($value)) {
            return;
        }
        $attribute =$this->getAttributes->forCode($value);
        if (null === $attribute) {
            return;
        }

        $authorizedTypes = (array)$constraint->types;
        if (!in_array($attribute->type(), $authorizedTypes)) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ invalid_type }}', $attribute->type())
                          ->setParameter('{{ expected_types }}', implode('|', $authorizedTypes))
                          ->addViolation();
        }
    }
}
