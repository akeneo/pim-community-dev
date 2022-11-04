<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint to check that the value is not empty (except for EMPTY operator)
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class NonEmptyValueCondition extends Constraint
{
    /** @var string */
    public $message = 'pimee_catalog_rule.rule_definition.validation.conditions.missing_value_key';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pimee_non_empty_value_validator';
    }
}
