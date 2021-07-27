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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement;

final class MeasurementUnitLabelSelection implements MeasurementSelectionInterface
{
    public const TYPE = 'unit_label';

    private string $measurementFamily;
    private string $locale;

    public function __construct(string $measurementFamily, string $locale)
    {
        $this->measurementFamily = $measurementFamily;
        $this->locale = $locale;
    }

    public function getMeasurementFamily(): string
    {
        return $this->measurementFamily;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getAllLocaleCodes(): array
    {
        return [$this->locale];
    }

    public function getAllAttributeCodes(): array
    {
        return [];
    }
}
