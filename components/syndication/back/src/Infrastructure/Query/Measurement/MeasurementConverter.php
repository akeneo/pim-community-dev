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

namespace Akeneo\Platform\Syndication\Infrastructure\Query\Measurement;

use Akeneo\Platform\Syndication\Domain\Query\MeasurementConverterInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;

class MeasurementConverter implements MeasurementConverterInterface
{
    private MeasureConverter $measureConverter;

    public function __construct(
        MeasureConverter $measureConverter
    ) {
        $this->measureConverter = $measureConverter;
    }

    public function convert(
        string $measurementFamilyCode,
        string $currentUnitCode,
        string $targetUnitCode,
        string $value
    ): string {
        $this->measureConverter->setFamily($measurementFamilyCode);

        return $this->measureConverter->convert($currentUnitCode, $targetUnitCode, (float) $value);
    }
}
