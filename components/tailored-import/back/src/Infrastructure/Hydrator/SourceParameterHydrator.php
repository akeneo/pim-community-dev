<?php


namespace Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\MeasurementSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\NumberSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\SourceParameterInterface;

class SourceParameterHydrator
{
    public function hydrate(?array $normalizedSourceParameter, string $attributeType): ?SourceParameterInterface
    {
        return match ($attributeType) {
            'pim_catalog_number' => $this->hydrateNumberSourceParameter($normalizedSourceParameter),
            'pim_catalog_metric' => $this->hydrateMeasurementSourceParameter($normalizedSourceParameter),
            default => null,
        };
    }

    private function hydrateNumberSourceParameter(array $normalizedSourceParameter): SourceParameterInterface
    {
        return new NumberSourceParameter($normalizedSourceParameter['decimal_separator']);
    }

    private function hydrateMeasurementSourceParameter(array $normalizedSourceParameter): SourceParameterInterface
    {
        return new MeasurementSourceParameter($normalizedSourceParameter['unit'], $normalizedSourceParameter['decimal_separator']);
    }
}
