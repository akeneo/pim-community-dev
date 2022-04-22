<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\MeasurementSourceConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\NumberSourceConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\SourceConfigurationInterface;

class SourceConfigurationHydrator
{
    public function hydrate(?array $normalizedSourceConfiguration, string $attributeType): ?SourceConfigurationInterface
    {
        return match ($attributeType) {
            'pim_catalog_number' => $this->hydrateNumberSourceConfiguration($normalizedSourceConfiguration),
            'pim_catalog_metric' => $this->hydrateMeasurementSourceConfiguration($normalizedSourceConfiguration),
            default => null,
        };
    }

    private function hydrateNumberSourceConfiguration(array $normalizedSourceConfiguration): SourceConfigurationInterface
    {
        return new NumberSourceConfiguration($normalizedSourceConfiguration['decimal_separator']);
    }

    private function hydrateMeasurementSourceConfiguration(array $normalizedSourceConfiguration): SourceConfigurationInterface
    {
        return new MeasurementSourceConfiguration($normalizedSourceConfiguration['unit'], $normalizedSourceConfiguration['decimal_separator']);
    }
}
