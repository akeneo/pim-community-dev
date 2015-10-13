<?php

namespace Pim\Component\Localization\Localizer;

/**
 * A localizer ;
 *    - check if data provided respects the expected format
 *    - convert a localized value to the default format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocalizerInterface
{
    /**
     * Data provided respects the expected format ?
     *
     * @param mixed $data
     * @param array $options
     *
     * @return bool
     */
    public function isValid($data, array $options);

    /**
     * Convert a localized value to the default format
     *
     * @param mixed $data
     *
     * @return array
     */
    public function convertLocalizedToDefault($data);

    /**
     * Whether or not the class supports the localizer
     *
     * @param string $attributeType
     *
     * @return bool
     */
    public function supports($attributeType);
}
