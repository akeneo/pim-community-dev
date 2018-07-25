<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOrder
{
    /** @var int */
    private $order;

    private function __construct(int $order)
    {
        Assert::natural($order, sprintf('An attribute order cannot be negative, %d given', $order));
        $this->order = $order;
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
