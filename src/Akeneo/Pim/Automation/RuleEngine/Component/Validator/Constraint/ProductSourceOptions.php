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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ProductSourceOptionsValidator;
use Symfony\Component\Validator\Constraint;

class ProductSourceOptions extends Constraint
{
    public $message = 'pimee_catalog_rule.rule_definition.validation.actions.concatenate.unexpected_source_option';

    public function validatedBy(): string
    {
        return ProductSourceOptionsValidator::class;
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
