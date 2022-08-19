<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetMeasurementsFamilyQueryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetMeasurementsFamilyQuery implements GetMeasurementsFamilyQueryInterface
{
    public function __construct(private MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $code, string $locale): ?array
    {
        try {
            $measurementFamily = $this->measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($code));
        } catch (MeasurementFamilyNotFoundException) {
            return null;
        }

        $normalizedMeasurementFamily = $measurementFamily->normalize();

        $unitNormalizer = fn (array $unit) => [
            'code' => $unit['code'],
            'label' => $unit['labels'][$locale] ?? \sprintf('[%s]', $unit['code']),
        ];

        return [
            'code' => $normalizedMeasurementFamily['code'],
            'measurements' => \array_map($unitNormalizer, $normalizedMeasurementFamily['units']),
        ];
    }
}
