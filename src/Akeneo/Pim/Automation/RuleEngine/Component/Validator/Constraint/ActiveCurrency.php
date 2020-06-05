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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ActiveCurrencyValidator;
use Symfony\Component\Validator\Constraint;

class ActiveCurrency extends Constraint
{
    public $message = 'Expected a valid currency, the "%currency%" currency does not exist or is not activated';

    public function validatedBy()
    {
        return ActiveCurrencyValidator::class;
    }
}
