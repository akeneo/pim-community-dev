<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement;

use Akeneo\Platform\TailoredExport\Domain\Query\MeasurementConverterInterface;

final class InMemoryMeasurementConverter implements MeasurementConverterInterface
{
    private array $conversions = [
        'Weight' => [
            'KILOGRAM' => [
                'GRAM' => 1000,
            ],
        ],
    ];

    public function convert(
        string $measurementFamilyCode,
        string $currentUnitCode,
        string $targetUnitCode,
        string $value
    ): string {
        return (string) ($this->conversions[$measurementFamilyCode][$currentUnitCode][$targetUnitCode] * (float) $value);
    }
}
