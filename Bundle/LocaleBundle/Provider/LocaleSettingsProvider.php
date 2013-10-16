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
     * Get name format based on locale, if locale is not passed default locale will be used. Fallback to return value
     * by full locale, locale language part and default locale (DEFAULT_LOCALE constant).
     *
     * @param string|null $locale
     * @throws \RuntimeException
     */
    public function getNameFormat($locale = null)
    {
        if (!$locale) {
            $locale = self::getDefaultLocale();
        }
        if (isset($this->nameFormats[$locale])) {
            // matched by locale (for example - "fr_CA")
            return $this->nameFormats[$locale];
        } else {
            $localeParts = \Locale::parseLocale($locale);
            if (isset($localeParts[\Locale::LANG_TAG], $this->nameFormats[$localeParts[\Locale::LANG_TAG]])) {
                // matched by locale language (for example - "fr")
                return $this->nameFormats[$localeParts[\Locale::LANG_TAG]];
            } elseif (isset($this->nameFormats[self::DEFAULT_LOCALE])) {
                // fallback to default language
                return $this->nameFormats[self::DEFAULT_LOCALE];
            } else {
                throw new \RuntimeException(sprintf('Cannot get name format for "%s"', $locale));
            }
        }
    }

    /**
     * Get name format based on locale, if locale is not passed default locale will be used. Fallback to return value
     * by full locale, locale region part and default locale (DEFAULT_LOCALE constant).
     *
     * @param string|null $localeOrRegion
     * @throws \RuntimeException
     */
    public function getAddressFormat($localeOrRegion = null)
    {
        if (!$localeOrRegion) {
            $localeOrRegion = self::getDefaultLocale();
        }
        if (isset($this->addressFormats[$localeOrRegion])) {
            // matched by country (for example - "RU")
            return $this->addressFormats[$localeOrRegion];
        } else {
            $localeParts = \Locale::parseLocale($localeOrRegion);
            if (isset($localeParts[\Locale::REGION_TAG], $this->addressFormats[$localeParts[\Locale::REGION_TAG]])) {
                // matched by locale region - "CA"
                return $this->addressFormats[$localeParts[\Locale::LANG_TAG]];
            } elseif (isset($this->addressFormats[self::DEFAULT_COUNTRY])) {
                // fallback to default country
                return $this->addressFormats[self::DEFAULT_COUNTRY];
            } else {
                throw new \RuntimeException(sprintf('Cannot get address format for "%s"', $localeOrRegion));
            }
        }
    }

    /**
     * @param int $attribute
     * @param null $locale
     */
    public static function getNumberFormatterAttribute($attribute, $locale = null)
    {

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

    /**
     * Get default locale
     *
     * @return string
     */
    public static function getDefaultLocale()
    {
        return \Locale::getDefault();
    }
}
