<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\Value\Measurement;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementFamilyCode;

interface MeasureConverter
{
    /**
     * @throws MeasurementUnitNotFoundException
     */
    public function convertAmountInStandardUnit(
        MeasurementFamilyCode $measurementFamilyCode,
        string $amount,
        string $unit
    ): string;
}
