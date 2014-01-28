<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Symfony\Component\Intl;
use Pim\Bundle\UserBundle\Context\UserContext;

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
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     * @param UserContext $userContext
     */
    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
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
            $locale = $this->userContext->getCurrentLocale();
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
            $locale = $this->userContext->getCurrentLocale();
        }
        $language = \Locale::getPrimaryLanguage($locale);

        return Intl\Intl::getCurrencyBundle()->getCurrencySymbol($currency, $language);
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
            $locale = $this->userContext->getCurrentLocale();
        }
        $language = \Locale::getPrimaryLanguage($locale);

        return Intl\Intl::getCurrencyBundle()->getCurrencyName($currency, $language);
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
            $locale = $this->userContext->getCurrentLocale();
        }
        $language = \Locale::getPrimaryLanguage($locale);

        return Intl\Intl::getCurrencyBundle()->getCurrencyNames($language);
    }

    /**
     * Returns the catalog locale currency
     *
     * @return string
     */
    public function getLocaleCurrency()
    {
        $locale = $this->userContext->getCurrentLocale();

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
            $locale = $this->userContext->getCurrentLocale();
        }
        $localeLabel = $this->getLocaleLabel($code, $locale);

        return sprintf(
            '<span class="flag-language"><i class="flag flag-%s"></i><span class="language">%s</span></span>',
            strtolower(\Locale::getRegion($code)),
            $fullLabel ? $localeLabel : \Locale::getPrimaryLanguage($code)
        );
    }
}
