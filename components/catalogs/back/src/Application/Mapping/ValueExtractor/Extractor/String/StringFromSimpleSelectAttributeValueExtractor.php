<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringFromSimpleSelectAttributeValueExtractor implements StringValueExtractorInterface
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
        /** @var string|null $value */
        $value = $product['raw_values'][$code][$scope][$locale] ?? null;

        if (null !== $value) {
            /** @var string $labelLocale */
            $labelLocale = $parameters['label_locale'] ?? '';

            $options = $this->getAttributeOptionsByCodeQuery->execute($code, [$value], $labelLocale);
            $optionsLabel = \array_column($options, 'label');

            $value = $optionsLabel[0] ?? \sprintf('[%s]', $value);
        }

        return $value;
    }

    public function getSupportedSourceType(): string
    {
        return self::SUPPORTED_SOURCE_TYPE_SIMPLE_SELECT;
    }

    public function getSupportedTargetType(): string
    {
        return self::SUPPORTED_TARGET_TYPE_STRING;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}
