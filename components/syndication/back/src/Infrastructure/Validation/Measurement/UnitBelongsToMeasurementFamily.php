<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Measurement;

use Symfony\Component\Validator\Constraint;

class UnitBelongsToMeasurementFamily extends Constraint
{
    public const FAMILY_DOES_NOT_EXIST = 'akeneo.syndication.validation.measurement.family.does_not_exist';
    public const UNIT_DOES_NOT_EXIST = 'akeneo.syndication.validation.measurement.unit.does_not_exist';

    public string $measurementFamilyCode;

    public function getRequiredOptions(): array
    {
        return ['measurementFamilyCode'];
    }
}
