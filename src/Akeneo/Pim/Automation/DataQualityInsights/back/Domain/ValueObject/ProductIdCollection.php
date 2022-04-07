<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Webmozart\Assert\Assert;

final class ProductIdCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array<ProductEntityIdInterface>
     */
    private array $productIds;

    private function __construct(array $productIds)
    {
        // Unique process is checking in test and working with Stringable::__toString() method.
        $this->productIds = array_values(array_unique($productIds));
    }

    public static function fromStrings(array $productIds): self
    {
        Assert::allString($productIds);
        $productIdList = array_map(fn($idString) => intval($idString), $productIds);

        return self::fromInts($productIdList);
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

    /**
     * @param ProductEntityIdInterface $productId
     */
    public static function fromProductId(ProductEntityIdInterface $productId): self
    {
        return self::fromProductIds([$productId]);
    }

    /**
     * @param array<ProductEntityIdInterface> $productIds
     */
    public static function fromProductIds(array $productIds): self
    {
        foreach ($productIds as $id) {
            Assert::isInstanceOf($id, ProductEntityIdInterface::class);
        }
        return new self($productIds);
    }

    // public function findByInt(int $productId): ?ProductId
    // {
    //     $result = array_values(array_filter(
    //         $this->productIds,
    //         function (ProductId $product) use ($productId) {
    //             return $product->toInt() === $productId;
    //         }
    //     ));

    //     if (!$result) {
    //         return null;
    //     }

    //     return $result[0];
    // }

    /**
     * @return array<ProductEntityIdInterface>
     */
    public function toArray(): array
    {
        return $this->productIds;
    }

    /**
     * @return array<string>
     */
    public function toArrayString(): array
    {
        return array_map(fn(ProductEntityIdInterface $productId) => strval($productId), $this->productIds);
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
}
