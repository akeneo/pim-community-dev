<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringFromReferenceEntitySingleLinkAttributeValueExtractor implements StringValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        // not supported in CE
        return null;
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_REFERENCE_ENTITY_SINGLE_LINK;
    }

    public function getSupportedSubSourceType(): ?string
    {
        return self::SUB_SOURCE_TYPE_ATTRIBUTE_TEXT;
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
