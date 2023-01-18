<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service\AttributeValueExtractor;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
final class AttributeValueExtractorRegistry
{
    /**
     * @param AttributeValueExtractorInterface[] $extractors
     */
    public function __construct(
        private readonly array $extractors,
    ) {
        foreach ($this->extractors as $extractor) {
            if (!$extractor instanceof AttributeValueExtractorInterface) {
                throw new \LogicException(
                    static::class . ' accepts only array of ' .
                    AttributeValueExtractorInterface::class . ' as argument.'
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
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($attributeType)) {
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
