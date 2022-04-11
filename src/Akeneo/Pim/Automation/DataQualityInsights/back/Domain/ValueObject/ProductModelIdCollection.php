<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Webmozart\Assert\Assert;

final class ProductModelIdCollection implements ProductEntityIdCollection
{
    /**
     * @var array<ProductEntityIdInterface>
     */
    private array $productIds;

    private function __construct(array $productIds)
    {
        // Unique process is checking in test and working with ProductId::class__toString() method.
        $this->productIds = array_values(array_unique($productIds));
    }

    public static function fromStrings(array $productIds): self
    {
        Assert::allString($productIds);

        return new self(array_map(fn($idString) => ProductModelId::fromString($idString), $productIds));
    }

    public static function fromInts(array $productIds): self
    {
        Assert::allInteger($productIds);

        return new self(array_map(fn($id) => new ProductModelId($id), $productIds));
    }

    public static function fromString(string $productId): self
    {
        return self::fromStrings([$productId]);
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
}
