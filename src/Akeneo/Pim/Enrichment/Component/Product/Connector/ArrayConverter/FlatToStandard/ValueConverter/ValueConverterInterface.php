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
     *
     * @return array
     */
    public function convert(array $attributeFieldInfo, $value);

    /**
     * Supports the field.
     *
     * @param string $attributeType
     *
     * @return bool
     */
    public function supportsField($attributeType);
}
