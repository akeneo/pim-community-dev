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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\NotBlankCurrencyValidator;
use Symfony\Component\Validator\Constraint;

class NotBlankCurrency extends Constraint
{
    public $message = 'The "{{ currencyProperty }}" key is missing or empty';
    public $attributeProperty;
    public $currencyProperty;

    public function getRequiredOptions()
    {
        return ['attributeProperty', 'currencyProperty'];
    }

    public function validatedBy()
    {
        return NotBlankCurrencyValidator::class;
    }

    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
