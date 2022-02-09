<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Webmozart\Assert\Assert;

final class ProductIdCollection implements \IteratorAggregate
{
    /**
     * @var array<ProductId>
     */
    private array $productIds;

    private function __construct(array $productIds)
    {
        // Unique process is checking in test and working with ProductId::class__toString() method.
        $this->productIds = array_values(array_unique($productIds));
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

    public static function fromProductIds(array $productIds): self
    {
        foreach ($productIds as $id) {
            Assert::isInstanceOf($id, ProductId::class);
        }
        return new self($productIds);
    }

    public static function fromProductId(ProductId $productId): self
    {
        return self::fromProductIds([$productId]);
    }

    public function addProductId(ProductId $productId): self
    {
        $this->productIds[] = $productId;

        return new self($this->productIds);
    }

    public function findByInt(int $productId): ?ProductId {
        $result = array_values(array_filter($this->productIds,
            function (ProductId $product) use ($productId) {
                return $product->toInt() === $productId;
        }));

        if (!$result) {
            return null;
        }

        return $result[0];
    }

    /**
     * @return array<ProductId>
     */
    public function toArray(): array
    {
        return $this->productIds;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->productIds);
    }
}
