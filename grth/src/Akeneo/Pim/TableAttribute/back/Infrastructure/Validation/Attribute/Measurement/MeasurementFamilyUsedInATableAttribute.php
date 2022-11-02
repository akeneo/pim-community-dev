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

final class MeasurementFamilyUsedInATableAttribute extends Constraint
{
    public string $message = 'pim_table_configuration.validation.table_configuration.measurement_family_unit_should_not_be_linked';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
