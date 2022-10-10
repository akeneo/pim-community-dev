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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\IsAttributeValidator;
use Symfony\Component\Validator\Constraint;

class IsAttribute extends Constraint
{
    public $message = 'pimee_catalog_rule.rule_definition.validation.attribute.unknown';

    public function validatedBy(): string
    {
        return IsAttributeValidator::class;
    }
}
