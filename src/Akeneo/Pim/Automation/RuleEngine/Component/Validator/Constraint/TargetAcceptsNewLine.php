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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\TargetAcceptsNewLineValidator;
use Symfony\Component\Validator\Constraint;

final class TargetAcceptsNewLine extends Constraint
{
    public $message = 'pimee_catalog_rule.rule_definition.validation.actions.concatenate.text_target_does_not_accept_new_line';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return TargetAcceptsNewLineValidator::class;
    }

    public function getTargets(): string|array
    {
        return [static::CLASS_CONSTRAINT];
    }
}
