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

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceParameterApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\MeasurementSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\SourceParameterInterface;

class MeasurementSourceParameterApplier implements SourceParameterApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applySourceParameter(SourceParameterInterface $sourceParameter, string $value): string
    {
        if (!$sourceParameter instanceof MeasurementSourceParameter) {
            throw new \InvalidArgumentException('Cannot apply Measurement source parameter on this value');
        }

        if (str_contains($value, self::DEFAULT_DECIMAL_SEPARATOR) && $sourceParameter->getDecimalSeparator() !== self::DEFAULT_DECIMAL_SEPARATOR) {
            throw new \InvalidArgumentException(sprintf('Unexpected valid decimal separator "%s" on this value', self::DEFAULT_DECIMAL_SEPARATOR));
        }

        return str_replace($sourceParameter->getDecimalSeparator(), static::DEFAULT_DECIMAL_SEPARATOR, $value);
    }

    public function supports(SourceParameterInterface $sourceParameter, string $value): bool
    {
        return $sourceParameter instanceof MeasurementSourceParameter;
    }
}
