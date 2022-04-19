<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class ProductModelIdCollection implements ProductEntityIdCollection
{
    /**
     * @var array<ProductEntityIdInterface>
     */
    private array $productIds;

    private function __construct(array $productIds)
    {
        $this->productIds = array_values(array_unique($productIds));
    }

    public static function fromStrings(array $productIds): self
    {
        return new self(array_map(fn ($productId) => ProductModelId::fromString((string) $productId), $productIds));
    }

    /**
     * @return array<ProductEntityIdInterface>
     */
    public function toArray(): array
    {
        return $this->productIds;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->productIds);
    }

    public function count(): int
    {
        return count($this->productIds);
    }

    public function isEmpty(): bool
    {
        return empty($this->productIds);
    }

    public function toArrayString(): array
    {
        return array_map(fn (ProductModelId $productId) => (string)$productId, $this->productIds);
    }
}
