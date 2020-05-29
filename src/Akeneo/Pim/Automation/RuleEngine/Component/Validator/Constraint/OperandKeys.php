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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\OperandKeysValidator;
use Symfony\Component\Validator\Constraint;

class OperandKeys extends Constraint
{
    public $requiredKeyMessage = 'One of the "value" or "field" keys is required, but both are missing or empty';
    public $onlyOneKeyExpectedKeyMessage = 'Only one of the "value" or "field" keys were expected, but both were provided';
    public $unexpectedKeyMessage = 'The "{{ key }}" key was unexpected';

    public function validatedBy()
    {
        return OperandKeysValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
