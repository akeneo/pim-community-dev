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

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceConfigurationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\MeasurementSourceConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\SourceConfigurationInterface;

class MeasurementSourceConfigurationApplier implements SourceConfigurationApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applySourceConfiguration(SourceConfigurationInterface $sourceConfiguration, string $value): string
    {
        if (!$sourceConfiguration instanceof MeasurementSourceConfiguration) {
            throw new \InvalidArgumentException('Cannot apply Measurement source configuration on this value');
        }

        if (str_contains($value, self::DEFAULT_DECIMAL_SEPARATOR) && self::DEFAULT_DECIMAL_SEPARATOR !== $sourceConfiguration->getDecimalSeparator()) {
            throw new \InvalidArgumentException(sprintf('Unexpected valid decimal separator "%s" on this value', self::DEFAULT_DECIMAL_SEPARATOR));
        }

        return str_replace($sourceConfiguration->getDecimalSeparator(), static::DEFAULT_DECIMAL_SEPARATOR, $value);
    }

    public function supports(SourceConfigurationInterface $sourceConfiguration, string $value): bool
    {
        return $sourceConfiguration instanceof MeasurementSourceConfiguration;
    }
}
