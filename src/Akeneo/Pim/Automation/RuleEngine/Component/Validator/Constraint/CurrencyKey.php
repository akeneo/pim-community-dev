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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\CurrencyKeyValidator;
use Symfony\Component\Validator\Constraint;

class CurrencyKey extends Constraint
{
    public $emptyKeyMessage = 'pimee_catalog_rule.rule_definition.validation.currency.missing_key';
    public $unexpectedKeyMessage = 'pimee_catalog_rule.rule_definition.validation.currency.unexpected_key';

    public $attributeProperty;
    public $currencyProperty;

    public function getRequiredOptions(): array
    {
        return ['attributeProperty', 'currencyProperty'];
    }

    public function validatedBy(): string
    {
        return CurrencyKeyValidator::class;
    }

    public function getTargets(): string|array
    {
        return static::CLASS_CONSTRAINT;
    }
}
