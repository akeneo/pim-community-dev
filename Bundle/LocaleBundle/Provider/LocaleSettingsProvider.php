<?php

namespace Oro\Bundle\LocaleBundle\Provider;

use Symfony\Component\Intl\Intl;

class LocaleSettingsProvider
{
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
     * - %region%       / %REGION%
     * - %region_code%  / %REGION_CODE%
     * - %country%      / %COUNTRY%
     * - %country_iso2% / %COUNTRY_ISO2%
     * - %country_iso3% / %COUNTRY_ISO3%
     *
     * Array format:
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

    /**
     * Try to parse locale and return it in format "language"_"script"_"region",
     * if locale is empty or cannot be parsed then return locale
     *
     * @param string $locale
     * @return string
     */
    public static function getValidLocale($locale = null)
    {
        $result = LocaleSettingsProvider::DEFAULT_LOCALE;

        if ($result !== $locale) {
            $localeParts = \Locale::parseLocale($locale);
            $localeKeys = array(\Locale::LANG_TAG, \Locale::SCRIPT_TAG, \Locale::REGION_TAG);
            $localeParts = array_intersect_key($localeParts, array_flip($localeKeys));

            if ($localeParts) {
                $result = implode('_', $localeParts);
            }
        }

        return $result;
    }

    /**
     * Get country by locale if country is supported, otherwise return default country (US)
     *
     * @param string $locale
     * @return string
     */
    public static function getCountryByLocale($locale)
    {
        $region = \Locale::getRegion($locale);
        $countries = Intl::getRegionBundle()->getCountryNames();
        if (array_key_exists($region, $countries)) {
            return $region;
        }

        return LocaleSettingsProvider::DEFAULT_COUNTRY;
    }
}
