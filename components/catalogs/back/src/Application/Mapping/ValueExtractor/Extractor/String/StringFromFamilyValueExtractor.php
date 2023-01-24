<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringFromFamilyValueExtractor implements StringValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        // @todo fetch family label
        return $product['family_code'] ?? null;
    }

    public function getSupportedType(): string
    {
        return 'family';
    }

    public function getSupportedTargetType(): string
    {
        return 'string';
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}
