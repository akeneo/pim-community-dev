<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOrder
{
    private function __construct(
        private int $order,
    ) {
        Assert::natural($order);
    }

    public static function fromInteger(int $order): self
    {
        return new self($order);
    }

    public function intValue(): int
    {
        return $this->order;
    }

    public function equals(AttributeOrder $order): bool
    {
        return $this->order === $order->order;
    }
}
