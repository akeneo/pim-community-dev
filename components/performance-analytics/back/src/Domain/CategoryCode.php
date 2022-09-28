<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Domain;

use Webmozart\Assert\Assert;

final class CategoryCode
{
    private function __construct(private string $code)
    {
        Assert::stringNotEmpty($code);
    }

    public static function fromString(string $code): CategoryCode
    {
        return new CategoryCode($code);
    }

    public function toString(): string
    {
        return $this->code;
    }
}
