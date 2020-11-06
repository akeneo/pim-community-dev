<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

/**
 * Converts data.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueConverterInterface
{
    /**
     * Converts value.
     *
     * @param array  $attributeFieldInfo
     * @param string $value
     */
    public function convert(array $attributeFieldInfo, string $value): array;

    /**
     * Supports the field.
     *
     * @param string $attributeType
     */
    public function supportsField(string $attributeType): bool;
}
