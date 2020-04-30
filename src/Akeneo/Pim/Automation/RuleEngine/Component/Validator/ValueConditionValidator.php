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

use Akeneo\Pim\Automation\RuleEngine\Bundle\Validator\Constraint\ValueCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Condition;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Validates if the field supports the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ValueConditionValidator extends ConstraintValidator
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $factory;

    public function __construct(
        ProductQueryBuilderFactoryInterface $factory
    ) {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($condition, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, ValueCondition::class);
        Assert::isInstanceOf($condition, Condition::class);

        try {
            $this->factory->create()->addFilter(
                $condition->field,
                $condition->operator,
                $condition->value,
                [
                    'locale' => $condition->locale,
                    'scope' => $condition->locale,
                ]
            );
        } catch (\Exception $e) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '%message%' => $e->getMessage(),
                ]
            )->addViolation();
        }
    }
}
