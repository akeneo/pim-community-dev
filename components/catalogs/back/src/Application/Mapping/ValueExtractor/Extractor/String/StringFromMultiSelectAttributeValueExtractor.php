<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringFromMultiSelectAttributeValueExtractor implements StringValueExtractorInterface
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
    ): null | string {
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
            $translatedValue = \array_values(\array_filter($options, fn ($v) => $v['code'] === $value));
            if (\count($translatedValue) > 0 && isset($translatedValue[0]['label'])) {
                $translatedValues[] = $translatedValue[0]['label'];
            } else {
                $translatedValues[] = \sprintf('[%s]', $value);
            }
        }

        return \implode(', ', $translatedValues);
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_MULTI_SELECT;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_STRING;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }

    /**
     * @param array<mixed> $rawValues
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
