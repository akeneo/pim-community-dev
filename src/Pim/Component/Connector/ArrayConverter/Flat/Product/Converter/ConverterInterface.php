<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

/**
 * Converts data
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConverterInterface
{
    /**
     * Converts value
     *
     * @param string $fieldNameInfo
     * @param string $value
     *
     * @return array
     */
    public function convert($fieldNameInfo, $value);

    /**
     * Supports the field
     *
     * @param string $fieldType
     *
     * @return bool
     */
    public function supportsField($fieldType);
}
