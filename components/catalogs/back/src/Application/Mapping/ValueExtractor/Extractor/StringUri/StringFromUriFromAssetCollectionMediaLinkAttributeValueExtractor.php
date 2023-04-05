<?php

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringUri;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;

class StringFromUriFromAssetCollectionMediaLinkAttributeValueExtractor implements StringValueExtractorInterface
{

    public function extract(array $product, string $code, ?string $locale, ?string $scope, ?array $parameters,): null|string
    {
        // not supported in CE
        return null;
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_ASSET_COLLECTION;
    }

    public function getSupportedSubSourceType(): ?string
    {
        return self::SUB_SOURCE_TYPE_ATTRIBUTE_MEDIA_LINK;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_STRING;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return self::TARGET_FORMAT_URI;
    }
}
