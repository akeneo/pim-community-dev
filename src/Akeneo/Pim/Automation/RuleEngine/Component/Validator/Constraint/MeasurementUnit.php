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
    public $notMetricAttributeMessage = 'Attribute {{ attributeCode }} does not expect a unit, {{ unitCode }} given';
    public $invalidUnitMessage = 'The "{{ unitCode }}" unit code does not exist or does not belong to the measurement family of the "{{ attributeCode }}" attribute';

    public $attributeProperty;
    public $unitProperty;

    public function getRequiredOptions()
    {
        return ['attributeProperty', 'unitProperty'];
    }

    public function validatedBy()
    {
        return MeasurementUnitValidator::class;
    }

    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
