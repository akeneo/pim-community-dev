<?php

namespace Oro\Bundle\LocaleBundle\Model;

use Symfony\Component\Intl\Intl;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfiguration;

class LocaleSettings
{
    const ADDRESS_FORMAT_KEY  = 'format';
    const PHONE_PREFIX_KEY    = 'phone_prefix';
    const DEFAULT_LOCALE_KEY  = 'default_locale';
    const CURRENCY_CODE_KEY   = 'currency_code';
    const CURRENCY_SYMBOL_PREPEND_KEY = 'currency_symbol_prepend';
    const CURRENCY_SYMBOL_KEY = 'symbol';

    /**
     * @var string[]
     */
    static protected $locales;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $timeZone;

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
     *          'default_locale' => '<defaultLocaleString>',
     *          'currency_code'  => '<currencyIso3SymbolsCode>',
     *          'currency_symbol_prepend' => true|false,
     *          'phone_prefix'   => '<phonePrefixString>', // optional
     *     ),
     * )
     *
     * @var array
     */
    protected $localeData = array();

    /**
     * Array format:
     * array(
     *     '<currencyIso3SymbolsCode>' => array(
     *          'symbol' => '<currencySymbol>',
     *     ),
     * )
     *
     * @var array
     */
    protected $currencyData = array();

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * Adds name formats.
     *
     * @param array $formats
     */
    public function addNameFormats(array $formats)
    {
        $this->nameFormats = array_merge($this->nameFormats, $formats);
    }

    /**
     * Get name formats.
     *
     * @return array
     */
    public function getNameFormats()
    {
        return $this->nameFormats;
    }

    /**
     * Adds address formats.
     *
     * @param array $formats
     */
    public function addAddressFormats(array $formats)
    {
        $this->addressFormats = array_merge($this->addressFormats, $formats);
    }

    /**
     * Get address formats.
     *
     * @return array
     */
    public function getAddressFormats()
    {
        return $this->addressFormats;
    }

    /**
     * Adds locale data.
     *
     * @param array $data
     */
    public function addLocaleData(array $data)
    {
        $this->localeData = array_merge($this->localeData, $data);
    }

    /**
     * Get locale data.
     *
     * @return array
     */
    public function getLocaleData()
    {
        return $this->localeData;
    }

    /**
     * Adds locale data.
     *
     * @param array $data
     */
    public function addCurrencyData(array $data)
    {
        $this->currencyData = array_merge($this->currencyData, $data);
    }

    /**
     * Get locale data.
     *
     * @return array
     */
    public function getCurrencyData()
    {
        return $this->currencyData;
    }

    /**
     * @return boolean
     */
    public function isFormatAddressByAddressCountry()
    {
        return $this->configManager->get('oro_locale.format_address_by_address_country', false);
    }

    /**
     * Gets locale by country
     *
     * @param string $country Country code
     * @return string
     */
    public function getLocaleByCountry($country)
    {
        if (isset($this->localeData[$country][self::DEFAULT_LOCALE_KEY])) {
            return $this->localeData[$country][self::DEFAULT_LOCALE_KEY];
        }
        return $this->getLocale();
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = $this->configManager->get('oro_locale.locale', LocaleConfiguration::DEFAULT_LOCALE);
        }
        return $this->locale;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        if (null === $this->language) {
            $this->language = $this->configManager->get('oro_locale.language', LocaleConfiguration::DEFAULT_LANGUAGE);
        }
        return $this->language;
    }

    /**
     * Get default country
     *
     * @return string
     */
    public function getCountry()
    {
        if (null === $this->country) {
            $this->country = $this->configManager->get(
                'oro_locale.country',
                self::getCountryByLocale($this->getLocale())
            );
        }
        return $this->country;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        if (null === $this->currency) {
            $this->currency = $this->configManager->get('oro_locale.currency', LocaleConfiguration::DEFAULT_CURRENCY);
        }
        return $this->currency;
    }

    /**
     * Get time zone
     *
     * @return string
     */
    public function getTimeZone()
    {
        if (null === $this->timeZone) {
            $this->timeZone = $this->configManager->get('oro_locale.timezone', date_default_timezone_get());
        }
        return $this->timeZone;
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
            $locale = LocaleConfiguration::DEFAULT_LOCALE;
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
            array($lang, $script, LocaleConfiguration::DEFAULT_COUNTRY),
            array($lang, LocaleConfiguration::DEFAULT_COUNTRY),
            array($lang),
            array(LocaleConfiguration::DEFAULT_LOCALE, LocaleConfiguration::DEFAULT_COUNTRY),
            array(LocaleConfiguration::DEFAULT_LOCALE),
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

        return LocaleConfiguration::DEFAULT_COUNTRY;
    }
}
