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

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierRegistry;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsValidSource;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class IsValidSourceValidator extends ConstraintValidator
{
    /** @var GetAttributes */
    private $getAttributes;

    /** @var ValueStringifierRegistry */
    private $valueStringifierRegistry;

    public function __construct(GetAttributes $getAttributes, ValueStringifierRegistry $valueStringifierRegistry)
    {
        $this->getAttributes = $getAttributes;
        $this->valueStringifierRegistry = $valueStringifierRegistry;
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, IsValidSource::class);
        if (null === $value || !is_string($value)) {
            return;
        }

        $attribute = $this->getAttributes->forCode($value);
        if (null !== $attribute && null === $this->valueStringifierRegistry->getStringifier($attribute->type())) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ field }}', $value)
                          ->addViolation();
        }
    }

}
