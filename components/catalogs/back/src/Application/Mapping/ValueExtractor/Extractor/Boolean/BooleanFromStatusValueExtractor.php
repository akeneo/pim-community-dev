<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Boolean;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\BooleanValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class BooleanFromStatusValueExtractor implements BooleanValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | bool {
        return $product['is_enabled'];
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_STATUS;
    }

    public function getSupportedSubSourceType(): ?string
    {
        return null;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_BOOLEAN;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}
