<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Category\GetProductCategoriesLabelsQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringFromCategoriesValueExtractor implements StringValueExtractorInterface
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
    ): null | string {
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

        return \implode(', ', $categoriesLabels);
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_CATEGORIES;
    }

    public function getSupportedSubSourceType(): ?string
    {
        return null;
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
