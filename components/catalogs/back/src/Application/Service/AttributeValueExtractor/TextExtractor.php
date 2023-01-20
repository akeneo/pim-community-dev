<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service\AttributeValueExtractor;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TextExtractor implements AttributeValueExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(
        array $product,
        string $attributeCode,
        string $attributeType,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        return $product['raw_values'][$attributeCode][$scope][$locale] ?? null;
    }

    public function supports(string $attributeType): bool
    {
        return \in_array($attributeType, [
            'pim_catalog_text',
            'pim_catalog_textarea',
        ]);
    }
}
