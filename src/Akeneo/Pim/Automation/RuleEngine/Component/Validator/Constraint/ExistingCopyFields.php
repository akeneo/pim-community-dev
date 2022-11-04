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
 * Validation constraint to check if the fromField and the toField have an existing copier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingCopyFields extends Constraint
{
    /** @var string */
    public $message = 'pimee_catalog_rule.rule_definition.validation.actions.copy.invalid_fields';

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
        return 'pimee_copy_fields_validator';
    }
}
