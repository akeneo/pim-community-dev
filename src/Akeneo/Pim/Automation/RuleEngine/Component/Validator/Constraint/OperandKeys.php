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
    public $requiredKeyMessage = 'pimee_catalog_rule.rule_definition.validation.actions.calculate.missing_operand_key';
    public $onlyOneKeyExpectedKeyMessage = 'pimee_catalog_rule.rule_definition.validation.actions.calculate.only_one_key_expected';
    public $unexpectedKeyMessage = 'pimee_catalog_rule.rule_definition.validation.actions.calculate.unexpected_key';

    public function validatedBy(): string
    {
        return OperandKeysValidator::class;
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
