<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryId
{
    public function __construct(private int $id)
    {
        Assert::greaterThan($id, 0);
    }

    public function getValue(): int
    {
        return $this->id;
    }
}
