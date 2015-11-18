<?php

namespace Pim\Component\Localization\Localizer;

/**
 * Convert localized attributes to default format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocalizedAttributeConverterInterface
{
    /**
     * Convert localized attributes to default format
     *
     * @param array $items
     * @param array $options
     *
     * @return mixed
     */
    public function convertLocalizedToDefaultValues(array $items, array $options = []);

    /**
     * Localize an attribute value
     *
     * @param string $code
     * @param mixed  $value
     * @param array  $options
     *
     * @return mixed
     */
    public function convertDefaultToLocalizedValue($code, $value, $options = []);
}
