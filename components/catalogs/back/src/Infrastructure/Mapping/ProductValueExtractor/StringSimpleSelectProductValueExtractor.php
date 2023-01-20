<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Mapping\ProductValueExtractor;

use Akeneo\Catalogs\Application\Mapping\ProductValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringSimpleSelectProductValueExtractor implements ProductValueExtractorInterface
{
    public function __construct(
        private GetAttributeOptionsByCodeQueryInterface $getAttributeOptionsByCodeQuery,
    ) {
    }

    public function extract(
        array $product,
        string $attributeCode,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        /** @var string|null $value */
        $value = $product['raw_values'][$attributeCode][$scope][$locale] ?? null;

        if (null !== $value) {
            /** @var string $labelLocale */
            $labelLocale = $parameters['label_locale'] ?? '';

            $options = $this->getAttributeOptionsByCodeQuery->execute($attributeCode, [$value], $labelLocale);
            $optionsLabel = \array_column($options, 'label');

            $value = $optionsLabel[0] ?? \sprintf('[%s]', $value);
        }

        return $value;
    }

    public function supports(string $attributeType, string $targetType, ?string $targetFormat): bool
    {
        return 'pim_catalog_simpleselect' === $attributeType
            && 'string' === $targetType
            && null === $targetFormat;
    }
}
