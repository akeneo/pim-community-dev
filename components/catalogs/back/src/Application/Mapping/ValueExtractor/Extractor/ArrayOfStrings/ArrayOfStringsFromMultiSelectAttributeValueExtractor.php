<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStringsValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ArrayOfStringsFromMultiSelectAttributeValueExtractor implements ArrayOfStringsValueExtractorInterface
{
    public function __construct(
        private GetAttributeOptionsByCodeQueryInterface $getAttributeOptionsByCodeQuery,
    ) {
    }

    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | array {
        /** @var array<mixed> $rawValues */
        $rawValues = $product['raw_values'][$code][$scope][$locale] ?? null;

        try {
            /** @var array<string> $values */
            $values = $this->convertRawValuesToStringArray($rawValues);
        } catch (\Exception) {
            return null;
        }

        /** @var string $labelLocale */
        $labelLocale = $parameters['label_locale'] ?? '';
        $options = $this->getAttributeOptionsByCodeQuery->execute($code, $values, $labelLocale);

        $translatedValues = [];
        foreach ($values as $value) {
            $translatedValue = \array_values(\array_filter($options, fn ($option): bool => $option['code'] === $value));
            $translatedValues[] = $translatedValue[0]['label'] ?? \sprintf('[%s]', $value);
        }

        return $translatedValues;
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_MULTI_SELECT;
    }

    public function getSupportedSubSourceType(): ?string
    {
        return null;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_ARRAY_OF_STRINGS;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }

    /**
     * @param array<mixed>|null $rawValues
     * @return array<string>
     */
    private function convertRawValuesToStringArray(?array $rawValues): array
    {
        if (!\is_array($rawValues)) {
            throw new \LogicException();
        }

        /** @var array<string> $values */
        $values = [];
        foreach ($rawValues as $rawValue) {
            if (!\is_string($rawValue)) {
                throw new \LogicException();
            }
            $values[] = $rawValue;
        }
        return $values;
    }
}
