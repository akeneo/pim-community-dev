<?php

namespace Oro\Bundle\LocaleBundle\Provider;

use Symfony\Component\Intl\Intl;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class LocaleSettingsProvider
{
    const ADDRESS_FORMAT_KEY = 'format';
    const CURRENCY_CODE_KEY  = 'currencyCode';
    const PHONE_PREFIX_KEY   = 'phonePrefix';
    const DEFAULT_LOCALE_KEY = 'defaultLocale';

    const DEFAULT_LOCALE  = 'en';
    const DEFAULT_COUNTRY = 'US';

    /**
     * @var \NumberFormatter[]
     */
    static protected $numberFormatters;

    /**
     * @var \IntlDateFormatter[]
     */
    static protected $dateFormatters;

    /**
     * @var string[]
     */
    static protected $locales;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var string
     */
    protected $defaultCountry;

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
     * - %street1%      / %STREET1%
     * - %street2%      / %STREET2%
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
     * Array format:
     * array(
     *     '<countryCode>' => array(
     *          'currencyCode'  => '<currencyCodeString>',  // optional
     *          'phonePrefix'   => '<phonePrefixString>',   // optional
     *          'defaultLocale' => '<defaultLocaleString>', // optional
     *     ),
     * )
     *
     * @var array
     */
    protected $localeData = array();

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

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
     * @param array $data
     */
    public function addLocaleData(array $data)
    {
        $this->localeData = array_merge($this->localeData, $data);
    }

    /**
     * Get name format based on locale, if locale is not passed locale from system configuration will be used.
     *
     * @param string|null $locale
     * @throws \RuntimeException
     */
    public function getNameFormat($locale = null)
    {
        if (!$locale) {
            $locale = $this->getDefaultLocale();
        }

        // match by locale (for example - "fr_CA")
        if (isset($this->nameFormats[$locale])) {
            return $this->nameFormats[$locale];
        }

        // match by locale language (for example - "fr")
        $localeParts = \Locale::parseLocale($locale);
        if (isset($localeParts[\Locale::LANG_TAG])) {
            $match = $localeParts[\Locale::LANG_TAG];
            if (isset($match, $this->nameFormats[$match])) {
                return $this->nameFormats[$match];
            }
        }

        // match by default locale in system configuration settings
        $match = $this->getDefaultLocale();
        if ($match !== $locale && isset($this->nameFormats[$match])) {
            return $this->nameFormats[$match];
        }

        // fallback to default constant locale
        $match = self::DEFAULT_LOCALE;
        if (isset($this->nameFormats[$match])) {
            return $this->nameFormats[$match];
        }

        throw new \RuntimeException(sprintf('Cannot get name format for "%s"', $locale));
    }

    /**
     * Get address format based on locale or region, if argument is not passed locale from
     * system configuration will be used.
     *
     * @param string|null $localeOrRegion
     * @throws \RuntimeException
     */
    public function getAddressFormat($localeOrRegion = null)
    {
        if (!$localeOrRegion) {
            $localeOrRegion = $this->getDefaultLocale();
        }

        // matched by country (for example - "RU")
        if (isset($this->addressFormats[$localeOrRegion][self::ADDRESS_FORMAT_KEY])) {
            return $this->addressFormats[$localeOrRegion][self::ADDRESS_FORMAT_KEY];
        }

        // matched by locale region - "CA"
        $localeParts = \Locale::parseLocale($localeOrRegion);
        if (isset($localeParts[\Locale::REGION_TAG])) {
            $match = $localeParts[\Locale::REGION_TAG];
            if (isset($match, $this->addressFormats[$match][self::ADDRESS_FORMAT_KEY])) {
                return $this->addressFormats[$match][self::ADDRESS_FORMAT_KEY];
            }
        }

        // match by default country in system configuration settings
        $match = $this->getDefaultCountry();
        if ($match !== $localeOrRegion && isset($this->addressFormats[$match][self::ADDRESS_FORMAT_KEY])) {
            return $this->addressFormats[$match][self::ADDRESS_FORMAT_KEY];
        }

        // fallback to default country
        $match = self::DEFAULT_COUNTRY;
        if (isset($this->addressFormats[$match][self::ADDRESS_FORMAT_KEY])) {
            return $this->addressFormats[$match][self::ADDRESS_FORMAT_KEY];
        }

        throw new \RuntimeException(sprintf('Cannot get address format for "%s"', $localeOrRegion));
    }

    /**
     * Gets a return value of NumberFormatter::getAttribute call with given parameters
     *
     * @param int $attribute Constant like NumberFormatter::MAX_FRACTION_DIGITS
     * @param string $locale Locale string line "en_US"
     * @param int $style Constant like \NumberFormatter::DECIMAL
     * @param string|null $pattern Pattern string if the chosen style requires a pattern
     * @return int
     */
    public static function getNumberFormatterAttribute($attribute, $locale, $style, $pattern = null)
    {
        $formatter = self::getNumberFormatter($locale, $style, $pattern);
        return $formatter->getAttribute($attribute);
    }

    /**
     * Gets a return value of NumberFormatter::getTextAttribute call with given parameters
     *
     * @param int $attribute Constant like NumberFormatter::MAX_FRACTION_DIGITS
     * @param string $locale Locale string line "en_US"
     * @param int $style Constant like \NumberFormatter::DECIMAL
     * @param string|null $pattern Pattern string if the chosen style requires a pattern
     * @return string
     */
    public static function getNumberFormatterTextAttribute($attribute, $locale, $style, $pattern = null)
    {
        $formatter = self::getNumberFormatter($locale, $style, $pattern);
        return $formatter->getTextAttribute($attribute);
    }

    /**
     * Gets single instance fo NumberFormatter by passed parameters
     *
     * @param $locale
     * @param $style
     * @param null $pattern
     * @return \NumberFormatter
     */
    protected static function getNumberFormatter($locale, $style, $pattern = null)
    {
        $key = sprintf("%s:%s:%s", $locale, $style, $pattern);
        if (!isset(self::$numberFormatters[$key])) {
            self::$numberFormatters[$key] = new \NumberFormatter($locale, $style, $pattern);
        }
        return self::$numberFormatters[$key];
    }

    /**
     * Get the pattern used for the IntlDateFormatter
     *
     * @param string $locale
     * @param int $dateType One of the constant of IntlDateFormatter: NONE, FULL, LONG, MEDIUM, SHORT
     * @param int $timeType One of the constant IntlDateFormatter: NONE, FULL, LONG, MEDIUM, SHORT
     * @return string
     */
    public static function getDatePattern($locale, $dateType, $timeType)
    {
        return self::getDateFormatter($locale, $dateType, $timeType)->getPattern();
    }

    /**
     * Gets single instance fo IntlDateFormatter by passed parameters
     *
     * @param string $locale
     * @param int $dateType
     * @param int $timeType
     * @return \IntlDateFormatter
     */
    protected static function getDateFormatter($locale, $dateType, $timeType)
    {
        $key = sprintf("%s:%s:%s", $locale, $dateType, $timeType);
        if (!isset(self::$numberFormatters[$key])) {
            self::$numberFormatters[$key] = new \IntlDateFormatter($locale, $dateType, $timeType);
        }
        return self::$numberFormatters[$key];
    }

    public function getLocaleByCountry($country)
    {
        return self::DEFAULT_LOCALE;
    }

    /**
     * Get default locale
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        if (null === $this->defaultLocale) {
            $this->defaultLocale = $this->configManager->get('oro_locale.locale', \Locale::getDefault());
        }
        return $this->defaultLocale;
    }

    /**
     * Set default locale
     *
     * @param string $locale
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * Get default country
     *
     * @return string
     */
    public function getDefaultCountry()
    {
        if (null === $this->defaultCountry) {
            $this->defaultCountry = $this->configManager->get('oro_locale.country', self::DEFAULT_COUNTRY);
        }
        return $this->defaultCountry;
    }

    /**
     * Set default country
     *
     * @param string $country
     */
    public function setDefaultCountry($country)
    {
        $this->defaultCountry = $country;
    }

    /**
     * Try to parse locale and return it in format "language"_"region",
     * if locale is empty or cannot be parsed then return locale
     *
     * @param string $locale
     * @return string
     * @throws \RuntimeException
     */
    public static function getValidLocale($locale = null)
    {
        if (!$locale) {
            $locale = self::DEFAULT_LOCALE;
        }

        $localeParts = \Locale::parseLocale($locale);
        $lang = null;
        $script = null;
        $region = null;

        if (isset($localeParts[\Locale::LANG_TAG])) {
            $lang = $localeParts[\Locale::LANG_TAG];
        }
        if (isset($localeParts[\Locale::SCRIPT_TAG])) {
            $script = $localeParts[\Locale::SCRIPT_TAG];
        }
        if (isset($localeParts[\Locale::REGION_TAG])) {
            $region = $localeParts[\Locale::REGION_TAG];
        }

        $variants = array(
            array($lang, $script, $region),
            array($lang, $region),
            array($lang, $script, self::DEFAULT_COUNTRY),
            array($lang, self::DEFAULT_COUNTRY),
            array($lang),
            array(self::DEFAULT_LOCALE, self::DEFAULT_COUNTRY),
            array(self::DEFAULT_LOCALE),
        );

        $locales = self::getLocales();
        foreach ($variants as $elements) {
            $locale = implode('_', array_filter($elements));
            if (isset($locales[$locale])) {
                return $locale;
            }
        }

        throw new \RuntimeException(sprintf('Cannot validate locale "%s"', $locale));
    }

    /**
     * Returns list of all locales
     *
     * @return string[]
     */
    public static function getLocales()
    {
        if (null === self::$locales) {
            self::$locales = array();
            foreach (Intl::getLocaleBundle()->getLocales() as $locale) {
                self::$locales[$locale] = $locale;
            }
        }
        return self::$locales;
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
