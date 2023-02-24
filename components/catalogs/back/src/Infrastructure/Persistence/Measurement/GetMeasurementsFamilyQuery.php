<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Measurement;

use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\FindMeasurementFamilies;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @phpstan-import-type RawMeasurementFamily from GetMeasurementsFamilyQueryInterface
 */
final class GetMeasurementsFamilyQuery implements GetMeasurementsFamilyQueryInterface
{
    public function __construct(private FindMeasurementFamilies $findMeasurementFamilies)
    {
    }

    public function execute(string $code, string $locale = 'en_US'): ?array
    {
        $measurementFamily = $this->findMeasurementFamilies->byCode($code);

        if (null === $measurementFamily) {
            return null;
        }

        $unitNormalizer = static fn (array $unit): array => [
            'code' => (string) $unit['code'],
            'label' => (string) ($unit['labels'][$locale] ?? \sprintf('[%s]', (string) $unit['code'])),
            'convert_from_standard' => $unit['convert_from_standard'],
        ];

        $normalizedUnits = \array_map($unitNormalizer, $measurementFamily->units);

        /** @var RawMeasurementFamily */
        return [
            'code' => $measurementFamily->code,
            'units' => $normalizedUnits,
            'standard_unit' => $measurementFamily->standardUnitCode,
        ];
    }
}
