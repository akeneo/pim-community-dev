<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementSourceConfiguration implements SourceConfigurationInterface
{
    public function __construct(
        private string $unit,
        private string $decimalSeparator,
    ) {
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }
}
