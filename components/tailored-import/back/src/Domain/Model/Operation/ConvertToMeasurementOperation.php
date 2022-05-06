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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

class ConvertToMeasurementOperation implements OperationInterface
{
    public const TYPE = 'convert_to_measurement';

    public function __construct(
        private string $decimalSeparator,
        private string $unit,
    ) {
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'decimal_separator' => $this->decimalSeparator,
            'unit' => $this->unit,
        ];
    }
}
