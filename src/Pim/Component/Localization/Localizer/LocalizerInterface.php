<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Component\Localization\Exception\FormatLocalizerException;

/**
 * A localizer ;
 *    - check if data provided respects the expected format
 *    - convert a localized value to the default format
 *    - convert a default value to a localized format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocalizerInterface
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d';

    const DEFAULT_DECIMAL_SEPARATOR = '.';

    /**
     * Data provided respects the expected format ?
     *
     * @param mixed  $data
     * @param array  $options
     * @param string $attributeCode
     *
     * @throws FormatLocalizerException
     *
     * @return bool
     */
    public function isValid($data, array $options = [], $attributeCode);

    /**
     * Convert a localized value to the default format
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function convertLocalizedToDefault($data, array $options = []);

    /**
     * Convert a default value to a localized format depending on options
     *
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function convertDefaultToLocalized($data, array $options = []);

    /**
     * Convert a default value to a localized format depending on a locale
     *
     * @param mixed  $data
     * @param string $locale
     *
     * @return mixed
     */
    public function convertDefaultToLocalizedFromLocale($data, $locale);

    /**
     * Whether or not the class supports the localizer
     *
     * @param string $attributeType
     *
     * @return bool
     */
    public function supports($attributeType);
}
