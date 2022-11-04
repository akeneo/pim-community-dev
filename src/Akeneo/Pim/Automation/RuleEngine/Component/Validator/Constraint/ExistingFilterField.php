<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint on a filter field.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingFilterField extends Constraint
{
    /** @var string */
    public $message = 'pimee_catalog_rule.rule_definition.validation.conditions.existing_field';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pimee_filter_field_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return static::CLASS_CONSTRAINT;
    }
}
