<?php


namespace Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\NumberSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\SourceParameterInterface;

class SourceParameterHydrator
{
    public function hydrate(?array $normalizedSourceParameter, string $attributeType): ?SourceParameterInterface
    {
        if (null === $normalizedSourceParameter) {
            return null;
        }

        return match ($attributeType) {
            'pim_catalog_number' => $this->hydrateNumberSourceParameter($normalizedSourceParameter),
            default => null,
        };
    }

    private function hydrateNumberSourceParameter(array $normalizedSourceParameter): SourceParameterInterface
    {
        return new NumberSourceParameter($normalizedSourceParameter['decimal_separator']);
    }
}
