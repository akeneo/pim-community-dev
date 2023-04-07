<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\NumberValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NumberFromNumberAttributeValueExtractor implements NumberValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | float | int {
        /** @var mixed $value */
        $value = $product['raw_values'][$code][$scope][$locale] ?? null;

        if (\is_numeric($value)) {
            $intValue = (int) $value;
            if ($intValue == $value) {
                return $intValue;
            }

            return (float) $value;
        }

        return null;
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_NUMBER;
    }

    public function getSupportedSubSourceType(): ?string
    {
        return null;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_NUMBER;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}
