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

namespace Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement;

final class MeasurementUnitLabelSelection implements MeasurementSelectionInterface
{
    public const TYPE = 'unit_label';

    public function __construct(
        private string $measurementFamilyCode,
        private string $locale,
    ) {
    }

    public function getMeasurementFamilyCode(): string
    {
        return $this->measurementFamilyCode;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
