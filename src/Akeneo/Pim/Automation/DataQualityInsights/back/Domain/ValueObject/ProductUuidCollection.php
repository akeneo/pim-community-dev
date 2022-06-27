<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductUuidCollection implements ProductEntityIdCollection
{
    /**
     * @var array<ProductUuid>
     */
    private array $productUuids;

    private function __construct(array $productUuids)
    {
        $this->productUuids = array_values(array_unique($productUuids));
    }

    public static function fromStrings(array $productUuids): self
    {
        Assert::allString($productUuids);

        return new self(array_map(fn (string $productUuid) => ProductUuid::fromString($productUuid), $productUuids));
    }

    public static function fromString(string $productUuid): self
    {
        return self::fromStrings([$productUuid]);
    }

    public static function fromProductUuid(ProductUuid $productUuid): self
    {
        return self::fromProductUuids([$productUuid]);
    }

    public static function fromProductUuids(array $productUuids): self
    {
        foreach ($productUuids as $productUuid) {
            Assert::isInstanceOf($productUuid, ProductUuid::class);
        }

        return new self($productUuids);
    }

    /**
     * @return array<ProductEntityIdInterface>
     */
    public function toArray(): array
    {
        return $this->productUuids;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->productUuids);
    }

    public function count(): int
    {
        return count($this->productUuids);
    }

    public function isEmpty(): bool
    {
        return empty($this->productUuids);
    }

    public function toArrayString(): array
    {
        return array_map(fn (ProductEntityIdInterface $productUuid) => (string) $productUuid, $this->productUuids);
    }

    public function toArrayBytes(): array
    {
        return array_map(fn (ProductEntityIdInterface $productUuid) => $productUuid->toBytes(), $this->productUuids);
    }
}
