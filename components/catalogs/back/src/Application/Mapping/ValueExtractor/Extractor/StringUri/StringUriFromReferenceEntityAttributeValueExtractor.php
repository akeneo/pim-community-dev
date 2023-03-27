<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringUri;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringUriFromReferenceEntityAttributeValueExtractor implements StringValueExtractorInterface
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
        return self::SOURCE_TYPE_ATTRIBUTE_REFERENCE_ENTITY;
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
        return self::TARGET_FORMAT_URI;
    }
}
