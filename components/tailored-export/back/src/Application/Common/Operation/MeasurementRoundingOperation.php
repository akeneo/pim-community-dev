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
    public const ROUNDING_TYPES = [self::TYPE_STANDARD, self::TYPE_ROUND_UP, self::TYPE_ROUND_DOWN];

    private const TYPE_STANDARD = 'standard';
    private const TYPE_ROUND_UP = 'round_up';
    private const TYPE_ROUND_DOWN = 'round_down';
    private const MIN_PRECISION_SUPPORTED = 0;
    private const MAX_PRECISION_SUPPORTED = 12;

    public function __construct(
        private string $type,
        private int $precision,
    ) {
        Assert::oneOf($type, self::ROUNDING_TYPES);
        Assert::greaterThanEq($precision, self::MIN_PRECISION_SUPPORTED);
        Assert::lessThanEq($precision, self::MAX_PRECISION_SUPPORTED);
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
