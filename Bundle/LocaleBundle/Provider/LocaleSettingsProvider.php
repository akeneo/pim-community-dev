<?php

namespace Oro\Bundle\LocaleBundle\Provider;

class LocaleSettingsProvider
{
    const NAME_FORMAT_FULL = 'full';
    const ADDRESS_FORMAT = 'format';
    const DEFAULT_LOCALE = 'en';
    const DEFAULT_COUNTRY = 'US';

    /**
     * Format placeholders (lowercase and uppercase):
     * - %prefix%      / %PREFIX%
     * - %first_name%  / %FIRST_NAME%
     * - %middle_name% / %MIDDLE_NAME%
     * - %last_name%   / %LAST_NAME%
     * - %suffix%      / %SUFFIX%
     *
     * Array format:
     * array(
     *     '<locale>' => '<formatString>',
     *     ...
     * )
     *
     * @var array
     */
    protected $nameFormats = array();

    /**
     * Format placeholders (lowercase and uppercase):
     * - %postal_code%  / %POSTAL_CODE%
     * - %name%         / %NAME%
     * - %organization% / %ORGANIZATION%
     * - %street%       / %STREET%
     * - %city%         / %CITY%
     * - %state%        / %STATE%
     * - %country%      / %COUNTRY%
     *
     * Format:
     * array(
     *     '<countryCode>' => array(
     *          'format' => '<formatString>',
     *          ...
     *     ),
     *     ...
     * )
     *
     * @var array
     */
    protected $addressFormats = array();

    /**
     * @param array $formats
     */
    public function addNameFormats(array $formats)
    {
        $this->nameFormats = array_merge($this->nameFormats, $formats);
    }

    /**
     * @param array $formats
     */
    public function addAddressFormats(array $formats)
    {
        $this->addressFormats = array_merge($this->addressFormats, $formats);
    }

    /**
     * Get name format based on locale.
     *
     * @param null|string $locale
     */
    public function getNameFormat($locale = null)
    {
        if (!$locale) {
            $locale = \Locale::getDefault();
        }
        if (isset($this->nameFormats[$locale])) {
            return $this->nameFormats[$locale];
        } else {
            return $this->nameFormats[self::DEFAULT_LOCALE];
        }
    }
}
