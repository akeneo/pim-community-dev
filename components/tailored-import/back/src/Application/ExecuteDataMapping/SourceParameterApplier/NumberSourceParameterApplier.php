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

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\NumberSourceParameter;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\SourceParameterInterface;

class NumberSourceParameterApplier implements SourceParameterApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applySourceParameter(SourceParameterInterface $sourceParameter, string $value): string
    {
        if (!$sourceParameter instanceof NumberSourceParameter) {
            throw new \InvalidArgumentException('Cannot apply Number source parameter on this value');
        }

        if (str_contains($value, self::DEFAULT_DECIMAL_SEPARATOR) && $sourceParameter->getDecimalSeparator() !== self::DEFAULT_DECIMAL_SEPARATOR) {
            throw new \InvalidArgumentException(sprintf('Unexpected valid decimal separator "%s" on this value', self::DEFAULT_DECIMAL_SEPARATOR));
        }

        return str_replace($sourceParameter->getDecimalSeparator(), static::DEFAULT_DECIMAL_SEPARATOR, $value);
    }

    public function supports(SourceParameterInterface $sourceParameter, string $value): bool
    {
        return $sourceParameter instanceof NumberSourceParameter;
    }
}
