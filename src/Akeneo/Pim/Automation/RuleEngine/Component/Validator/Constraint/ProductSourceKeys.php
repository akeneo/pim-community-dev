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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ProductSourceKeysValidator;
use Symfony\Component\Validator\Constraint;

final class ProductSourceKeys extends Constraint
{
    public $missingSourceKeyMessage = 'pimee_catalog_rule.rule_definition.validation.actions.concatenate.missing_source_key';
    public $onlyOneSourceKeyExpectedMessage = 'pimee_catalog_rule.rule_definition.validation.actions.concatenate.only_one_source_key_expected';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return ProductSourceKeysValidator::class;
    }

    public function getTargets(): string|array
    {
        return [static::CLASS_CONSTRAINT];
    }
}
