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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Webmozart\Assert\Assert;

final class MeasurementUnitCode
{
    private function __construct(private string $code)
    {
    }

    public static function fromString(string $code): self
    {
        Assert::stringNotEmpty($code);

        return new self($code);
    }

    public function asString(): string
    {
        return $this->code;
    }
}
