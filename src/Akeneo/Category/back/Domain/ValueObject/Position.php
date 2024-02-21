<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Position
{
    public function __construct(public readonly int $left, public readonly int $right, public readonly int $level)
    {
        Assert::notNull($left);
        Assert::notNull($right);
        Assert::greaterThan($left, 0);
        Assert::greaterThan($right, 0);
        Assert::notNull($level);
    }
}
