<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringDateTime;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringDateTimeFromDateAttributeValueExtractor implements StringValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        /** @var mixed $value */
        $value = $product['raw_values'][$code][$scope][$locale] ?? null;

        // @todo transform date in ISO 8601

        return null !== $value ? (string) $value : null;
    }

    public function getSupportedType(): string
    {
        return 'pim_catalog_date';
    }

    public function getSupportedTargetType(): string
    {
        return 'string';
    }

    public function getSupportedTargetFormat(): ?string
    {
        return 'date-time';
    }
}
