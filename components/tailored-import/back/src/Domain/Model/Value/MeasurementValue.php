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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Webmozart\Assert\Assert;

class MeasurementValue implements ValueInterface
{
    private const TYPE = 'measurement';

    public function __construct(
        private string $value,
        private string $unit,
    ) {
        Assert::stringNotEmpty($value);
        Assert::stringNotEmpty($unit);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'value' => $this->value,
            'unit' => $this->unit,
        ];
    }
}
