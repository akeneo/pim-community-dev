<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Symfony\Component\Validator\Constraint;

final class MeasurementUnitShouldExist extends Constraint
{
    public string $message = 'pim_table_configuration.validation.table_configuration.measurement_unit_should_exist';
    public string $measurementFamilyCodeKey = 'measurement_family_code';
    public string $measurementUnitCodeKey = 'measurement_default_unit_code';

    public function getTargets(): array
    {
        return [self::PROPERTY_CONSTRAINT];
    }
}
