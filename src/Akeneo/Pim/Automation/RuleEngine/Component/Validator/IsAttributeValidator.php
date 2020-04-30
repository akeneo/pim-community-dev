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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class IsAttributeValidator extends ConstraintValidator
{
    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, IsAttribute::class);
        if (null === $value || !is_string($value)) {
            return;
        }

        $attribute = $this->getAttributes->forCode($value);
        if (null === $attribute) {
            $this->context->buildViolation($constraint->unknownAttributeMessage)
                          ->setParameter('{{ code }}', $value)
                          ->addViolation();

            return;
        }

        if ($constraint->types) {
            $authorizedTypes = (array)$constraint->types;

            if (!in_array($attribute->type(), $authorizedTypes)) {
                $this->context->buildViolation($constraint->invalidTypeMessage)
                              ->setParameter('{{ invalid_type }}', $attribute->type())
                              ->setParameter('{{ expected_types }}', implode('|', $authorizedTypes))
                              ->addViolation();
            }
        }
    }
}
