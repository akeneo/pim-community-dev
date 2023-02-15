<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetCachedCategoriesByCodesQuery;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetCachedCategoryCodesByProductUuidsQuery;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringFromCategoriesValueExtractor implements StringValueExtractorInterface
{
    public function __construct(
        private GetCachedCategoryCodesByProductUuidsQuery $getCachedCategoryCodesByProductUuidsQuery,
        private GetCachedCategoriesByCodesQuery $getCachedCategoriesByCodesQuery,
    ) {
    }

    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        $categoriesCodes = \array_values(
            $this->getCachedCategoryCodesByProductUuidsQuery->fetch([$product['uuid']]),
        )[0];
        if (\count($categoriesCodes) === 0 || $locale === null) {
            return null;
        }
        $categoriesLabels = \array_column(
            $this->getCachedCategoriesByCodesQuery->fetch($categoriesCodes, $locale),
            'label',
        );
        return \implode(', ', $categoriesLabels);
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_CATEGORIES;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_STRING;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}
