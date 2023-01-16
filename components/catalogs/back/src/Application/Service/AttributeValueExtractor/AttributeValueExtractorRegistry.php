<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service\AttributeValueExtractor;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductsQueryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductsQueryInterface
 */
final class AttributeValueExtractorRegistry
{
    /**
     * @param AttributeValueExtractorInterface[] $extractors
     */
    public function __construct(
        private array $extractors,
    ) {
        foreach ($this->extractors as $extractor) {
            if ($extractor instanceof AttributeValueExtractorInterface) {
                throw new \LogicException(
                    static::class . ' accepts only array of ' .
                    AttributeValueExtractorInterface::class . ' as argument.'
                );
            }
        }
    }

    /**
     * @param RawProduct $product
     */
    public function extract(
        array $product,
        string $attributeCode,
        string $attributeType,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        foreach ($this->extractors as $extractor) {
            if ($extractor->support($attributeType)) {
                return $extractor->extract(
                    $product,
                    $attributeCode,
                    $attributeType,
                    $locale,
                    $scope,
                    $parameters,
                );
            }
        }

        throw new AttributeValueExtractorNotFoundException();
    }
}
