<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Mapping;

use Akeneo\Catalogs\Application\Mapping\ProductValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
final class ProductValueExtractorRegistry
{
    /**
     * @param ProductValueExtractorInterface[] $extractors
     */
    public function __construct(
        private readonly array $extractors,
    ) {
        foreach ($this->extractors as $extractor) {
            if (!$extractor instanceof ProductValueExtractorInterface) {
                throw new \LogicException(
                    static::class . ' accepts only array of ' .
                    ProductValueExtractorInterface::class . ' as argument.'
                );
            }
        }
    }

    /**
     * @param RawProduct $product
     * @param array<string, mixed>|null $parameters
     */
    public function extract(
        array $product,
        string $attributeCode,
        string $attributeType,
        string $targetType,
        ?string $targetFormat,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($attributeType, $targetType, $targetFormat)) {
                return $extractor->extract(
                    $product,
                    $attributeCode,
                    $locale,
                    $scope,
                    $parameters,
                );
            }
        }

        throw new ProductValueExtractorNotFoundException();
    }
}
