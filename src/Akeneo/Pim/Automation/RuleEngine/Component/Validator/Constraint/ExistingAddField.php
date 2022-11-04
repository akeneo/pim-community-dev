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
 * Validation constraint on a field on which you want to add a collection of items.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingAddField extends Constraint
{
    /** @var string */
    public $message = 'pimee_catalog_rule.rule_definition.validation.actions.add.invalid_field';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pimee_add_field_validator';
    }
}
