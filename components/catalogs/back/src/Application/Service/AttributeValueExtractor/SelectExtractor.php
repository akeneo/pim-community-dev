<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service\AttributeValueExtractor;

use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
final class SelectExtractor implements AttributeValueExtractorInterface
{
    public function __construct(
        private GetAttributeOptionsByCodeQueryInterface $getAttributeOptionsByCodeQuery,
    ) {
    }

    public function extract(
        array $product,
        string $attributeCode,
        string $attributeType,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {

        /** @var string|array<string>|null $value */
        $value = $product['raw_values'][$attributeCode][$scope][$locale] ?? null;
        if ($value !== null) {
            /** @var string $labelLocale */
            $labelLocale = $parameters['label_locale'] ?? '';
            $value = $this->getTranslations(
                attributeCode: $attributeCode,
                optionCode: \is_array($value) ? $value : [$value],
                locale: $labelLocale,
            );
        }

        return $value;
    }

    public function supports(string $attributeType): bool
    {
        return $attributeType === 'pim_catalog_simpleselect';
    }

    /**
     * @param array<string> $optionCode
     */
    private function getTranslations(string $attributeCode, array $optionCode, string $locale): string
    {
        $options = $this->getAttributeOptionsByCodeQuery->execute($attributeCode, $optionCode, $locale);
        $optionsLabel = \array_column($options, 'label');

        return \implode(', ', $optionsLabel);
    }
}
