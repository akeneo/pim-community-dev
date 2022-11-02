<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint checking the 'include_children' option of a remove action
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class IncludeChildrenOption extends Constraint
{
    public $invalidFieldMessage = 'pimee_catalog_rule.rule_definition.validation.actions.remove.include_children_option';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pimee_include_children_option_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return static::CLASS_CONSTRAINT;
    }
}
