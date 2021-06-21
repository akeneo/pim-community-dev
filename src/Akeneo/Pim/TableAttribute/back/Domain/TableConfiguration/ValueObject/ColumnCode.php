<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class ColumnCode
{
    private string $code;

    private function __construct(string $code)
    {
        $this->code = $code;
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

    public function equals(ColumnCode $otherColumnCode): bool
    {
        return $this->asString() === $otherColumnCode->asString();
    }
}
