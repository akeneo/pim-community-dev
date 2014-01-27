<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Symfony\Component\Intl;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

/**
 * LocaleHelper essentially allow to translate locale code to localized locale label
 *
 * Static locales are not initialized on the constructor because
 * when LocaleHelper is constructed, the user is not yet initialized
 * and by the way don't have locale code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleHelper
{
    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * Constructor
     * @param LocaleManager $localeManager
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * Initialized the locales list (if needed) and get the localized label
     *
     * @param string $code   the code of the local's label
     * @param string $locale the locale in which the label should be translated
     *
     * @return string
     */
    public function getLocaleLabel($code, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->localeManager->getCurrentLocale();
        }

        return \Locale::getDisplayName($code, $locale);
    }

    /**
     * Returns the symbol for a currency
     *
     * @param string $currency
     * @param string $locale
     *
     * @return string
     */
    public function getCurrencySymbol($currency, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->localeManager->getCurrentLocale();
        }
        $langage = \Locale::getPrimaryLanguage();

        return Intl\Intl::getCurrencyBundle()->getCurrencySymbol($currency, $langage);
    }

    /**
     * Returns the label for a currency
     *
     * @param string $currency
     * @param string $locale
     *
     * @return string
     */
    public function getCurrencyLabel($currency, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->localeManager->getCurrentLocale();
        }
        $langage = \Locale::getPrimaryLanguage();

        return Intl\Intl::getCurrencyBundle()->getCurrencyName($currency, $langage);
    }

    /**
     * Returns an array of all known currency names, indexed by code
     *
     * @param string $locale
     *
     * @return string
     */
    public function getCurrencyLabels($locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->localeManager->getCurrentLocale();
        }
        $langage = \Locale::getPrimaryLanguage($locale);

        return Intl\Intl::getCurrencyBundle()->getCurrencyNames($langage);
    }

    /**
     * Returns the catalog locale currency
     *
     * @return string
     */
    public function getLocaleCurrency()
    {
        $locale = $this->localeManager->getUserLocale();

        return (null !== $locale && $locale->getDefaultCurrency()) ? $locale->getDefaultCurrency()->getCode() : null;
    }

    /**
     * Returns the flag icon for a locale
     *
     * @param string  $code
     * @param boolean $fullLabel
     * @param string  $locale
     *
     * @return string
     */
    public function getFlag($code, $fullLabel = false, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->localeManager->getCurrentLocale();
        }
        $localeLabel = $this->getLocaleLabel($code, $locale);

        return sprintf(
            '<span class="flag-language"><i class="flag flag-%s"></i><span class="language">%s</span></span>',
            strtolower(\Locale::getRegion($code)),
            $fullLabel ? $localeLabel : \Locale::getPrimaryLanguage($code)
        );
    }
}
