<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Common\Operation;

use Webmozart\Assert\Assert;

class MeasurementRoundingOperation implements OperationInterface
{
    const TYPE_STANDARD = 'standard';
    const TYPE_UP = 'up';
    const TYPE_DOWN = 'down';

    public function __construct(
        private string $type,
        private int $precision
    ) {
        Assert::oneOf($type, [self::TYPE_STANDARD, self::TYPE_UP, self::TYPE_DOWN]);
        Assert::greaterThanEq($precision, 0);
        Assert::lessThanEq($precision, 12);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }
}
