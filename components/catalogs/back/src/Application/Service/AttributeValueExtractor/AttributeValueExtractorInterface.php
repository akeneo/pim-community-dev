<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service\AttributeValueExtractor;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
interface AttributeValueExtractorInterface
{
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
    ): null | string;

    public function supports(string $attributeType): bool;
}
