<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryPosition
{
    public function __construct(private readonly int $position)
    {
        Assert::greaterThan($position, 0);
    }

    public function getValue(): int
    {
        return $this->position;
    }
}
