<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStringsValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Category\GetProductCategoriesLabelsQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ArrayOfStringsFromCategoriesValueExtractor implements ArrayOfStringsValueExtractorInterface
{
    public function __construct(
        private readonly GetProductCategoriesLabelsQueryInterface $getProductCategoriesLabelsQuery,
    ) {
    }

    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | array {
        if (!\is_array($parameters) ||
            !isset($parameters['label_locale']) ||
            !\is_string($parameters['label_locale'])
        ) {
            return null;
        }

        $uuid = $product['uuid']->toString();
        $categoriesLabels = $this->getProductCategoriesLabelsQuery->execute($uuid, $parameters['label_locale']);
        if ([] === $categoriesLabels) {
            return null;
        }

        return $categoriesLabels;
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_CATEGORIES;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_ARRAY_OF_STRINGS;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}
