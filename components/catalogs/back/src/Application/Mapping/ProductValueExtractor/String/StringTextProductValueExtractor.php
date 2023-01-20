<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ProductValueExtractor\String;

use Akeneo\Catalogs\Application\Mapping\ProductValueExtractor\StringProductValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringTextProductValueExtractor implements StringProductValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        return $product['raw_values'][$code][$scope][$locale] ?? null;
    }

    public function supports(string $sourceType): bool
    {
        return 'pim_catalog_text' === $sourceType;
    }
}
