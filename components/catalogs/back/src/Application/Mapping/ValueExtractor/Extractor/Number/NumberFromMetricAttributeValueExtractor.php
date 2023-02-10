<?php

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\NumberValueExtractorInterface;

class NumberFromMetricAttributeValueExtractor implements NumberValueExtractorInterface
{

    public function extract(array $product, string $code, ?string $locale, ?string $scope, ?array $parameters,): null|float|int
    {
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
        return self::SOURCE_TYPE_ATTRIBUTE_METRIC;
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
