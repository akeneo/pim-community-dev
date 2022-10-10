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

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\MeasurementUnitValidator;
use Symfony\Component\Validator\Constraint;

class MeasurementUnit extends Constraint
{
    public $notMetricAttributeMessage = 'pimee_catalog_rule.rule_definition.validation.measurement.invalid_attribute_type';
    public $invalidUnitMessage = 'pimee_catalog_rule.rule_definition.validation.measurement.invalid_unit';

    public $attributeProperty;
    public $unitProperty;

    public function getRequiredOptions(): array
    {
        return ['attributeProperty', 'unitProperty'];
    }

    public function validatedBy(): string
    {
        return MeasurementUnitValidator::class;
    }

    public function getTargets(): string|array
    {
        return static::CLASS_CONSTRAINT;
    }
}
