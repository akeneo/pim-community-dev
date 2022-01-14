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

namespace Akeneo\Platform\Syndication\Application\Common\Selection\Measurement;

final class MeasurementValueAndUnitSymbolSelection implements MeasurementSelectionInterface
{
    public const TYPE = 'value_and_unit_symbol';

    private string $decimalSeparator;
    private string $measurementFamilyCode;

    public function __construct(string $decimalSeparator, string $measurementFamilyCode)
    {
        $this->decimalSeparator = $decimalSeparator;
        $this->measurementFamilyCode = $measurementFamilyCode;
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    public function getMeasurementFamilyCode(): string
    {
        return $this->measurementFamilyCode;
    }
}
