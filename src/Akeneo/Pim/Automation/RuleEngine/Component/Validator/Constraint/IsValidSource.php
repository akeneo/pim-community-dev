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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\IsValidSourceValidator;
use Symfony\Component\Validator\Constraint;

class IsValidSource extends Constraint
{
    public $message = 'pimee_catalog_rule.rule_definition.validation.actions.concatenate.invalid_source';

    public function validatedBy(): string
    {
        return IsValidSourceValidator::class;
    }
}
