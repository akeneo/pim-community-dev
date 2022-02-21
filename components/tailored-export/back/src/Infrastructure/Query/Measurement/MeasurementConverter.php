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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Measurement;

use Akeneo\Platform\TailoredExport\Domain\Query\MeasurementConverterInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;

class MeasurementConverter implements MeasurementConverterInterface
{
    public function __construct(
        private MeasureConverter $measureConverter,
    ) {
    }

    public function convert(
        string $measurementFamilyCode,
        string $currentUnitCode,
        string $targetUnitCode,
        string $value,
    ): string {
        $this->measureConverter->setFamily($measurementFamilyCode);

        return $this->measureConverter->convert($currentUnitCode, $targetUnitCode, (float) $value);
    }
}
