<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ProductValueExtractor\Number;

use Akeneo\Catalogs\Application\Mapping\ProductValueExtractor\NumberProductValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NumberNumberProductValueExtractor implements NumberProductValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | float | int {
        $value = $product['raw_values'][$code][$scope][$locale] ?? null;

        // @todo check if value is "floatable" or "intable" (i.e if it's a string that can be parsed)
        return null !== $value ? (float) $value : null;
    }

    public function supports(string $sourceType): bool
    {
        return 'pim_catalog_number' === $sourceType;
    }
}
