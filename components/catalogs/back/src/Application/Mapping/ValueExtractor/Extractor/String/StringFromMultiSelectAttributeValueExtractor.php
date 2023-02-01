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
        /** @var mixed $values */
        $values = $product['raw_values'][$code][$scope][$locale] ?? null;

        if (!\is_array($values) || $this->containsIncorrectType($values)) {
            return null;
        }

        $translatedValues = [];

        /** @var string $labelLocale */
        $labelLocale = $parameters['label_locale'] ?? '';
        $options = $this->getAttributeOptionsByCodeQuery->execute($code, $values, $labelLocale);
        if (!is_array($options) || count($options) === 0) {
            return null;
        }

        foreach ($values as $value) {
            $translatedValue = \array_filter($options, fn ($v) => $v['code'] == $value);
            $translatedValues[] = $translatedValue[\array_key_first($translatedValue)]['label'] ?? \sprintf('[%s]', $value);
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
     * @param array<mixed> $values
     */
    private function containsIncorrectType(array $values): bool
    {
        foreach ($values as $value) {
            if (!\is_string($value)) {
                return true;
            }
        }
        return false;
    }
}
