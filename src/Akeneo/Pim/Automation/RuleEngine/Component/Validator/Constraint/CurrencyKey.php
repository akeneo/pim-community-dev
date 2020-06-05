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
    public $emptyKeyMessage = 'The "{{ key }}" key is missing or empty';
    public $unexpectedKeyMessage = 'The {{ key }} key was unexpected';
    public $attributeProperty;
    public $currencyProperty;

    public function getRequiredOptions()
    {
        return ['attributeProperty', 'currencyProperty'];
    }

    public function validatedBy()
    {
        return CurrencyKeyValidator::class;
    }

    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
