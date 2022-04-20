<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Webmozart\Assert\Assert;

final class ProductIdCollection implements ProductEntityIdCollection
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
        Assert::allString($productIds);

        return new self(array_map(fn (string $productId) => ProductId::fromString($productId), $productIds));
    }

    public static function fromInts(array $productIds): self
    {
        $productIdList = [];
        Assert::allInteger($productIds);
        foreach ($productIds as $id) {
            $productIdList[] = new ProductId($id);
        }

        return new self($productIdList);
    }

    public static function fromInt(int $productId): self
    {
        return self::fromInts([$productId]);
    }

    public static function fromString(string $productId): self
    {
        return self::fromStrings([$productId]);
    }

    public static function fromProductId(ProductId $productId): self
    {
        return self::fromProductIds([$productId]);
    }

    public static function fromProductIds(array $productIds): self
    {
        foreach ($productIds as $id) {
            Assert::isInstanceOf($id, ProductId::class);
        }

        return new self($productIds);
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
        return array_map(fn (ProductId $productId) => (string) $productId, $this->productIds);
    }
}
