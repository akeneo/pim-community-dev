<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Validator\Constraint\NonEmptyValueCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Condition;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Validates that a value is not empty (except if operator is EMPTY or NOT EMPTY)
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class NonEmptyValueConditionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($condition, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, NonEmptyValueCondition::class);
        Assert::isInstanceOf($condition, Condition::class);

        $value = $condition->value;

        if (Operators::IS_EMPTY !== $condition->operator &&
            Operators::IS_NOT_EMPTY !== $condition->operator &&
            null === $value
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
