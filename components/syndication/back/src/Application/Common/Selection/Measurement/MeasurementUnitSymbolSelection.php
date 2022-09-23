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

final class MeasurementUnitSymbolSelection implements MeasurementSelectionInterface
{
    public const TYPE = 'unit_symbol';

    private string $measurementFamilyCode;

    public function __construct(string $measurementFamilyCode)
    {
        $this->measurementFamilyCode = $measurementFamilyCode;
    }

    public function getMeasurementFamilyCode(): string
    {
        return $this->measurementFamilyCode;
    }
}
