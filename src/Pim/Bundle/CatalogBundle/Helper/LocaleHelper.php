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
     * Returns the current locale
     *
     * @return string
     */
    public function getCurrentLocaleCode()
    {
        return $this->userContext->getCurrentLocaleCode();
    }

    /**
     * Returns the label of a locale in the specified language
     *
     * @param string $code       the code of the locale to translate
     * @param string $localeCode the locale in which the label should be translated
     *
     * @return string
     */
    public function getLocaleLabel($code, $localeCode = null)
    {
        $localeCode = $localeCode ?: $this->getCurrentLocaleCode();

        return \Locale::getDisplayName($code, $localeCode);
    }

    /**
     * Returns the symbol for a currency
     *
     * @param string $currency
     * @param string $localeCode
     *
     * @return string
     */
    public function getCurrencySymbol($currency, $localeCode = null)
    {
        $localeCode = $localeCode ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($localeCode);

        return Intl\Intl::getCurrencyBundle()->getCurrencySymbol($currency, $language);
    }

    /**
     * Returns the label for a currency
     *
     * @param string $currency
     * @param string $localeCode
     *
     * @return string
     */
    public function getCurrencyLabel($currency, $localeCode = null)
    {
        $localeCode = $localeCode ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($localeCode);

        return Intl\Intl::getCurrencyBundle()->getCurrencyName($currency, $language);
    }

    /**
     * Returns an array of all known currency names, indexed by code
     *
     * @param string $localeCode
     *
     * @return string
     */
    public function getCurrencyLabels($localeCode = null)
    {
        $localeCode = $localeCode ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($localeCode);

        return Intl\Intl::getCurrencyBundle()->getCurrencyNames($language);
    }

    /**
     * Returns the flag icon for a locale
     *
     * @param string  $code
     * @param boolean $fullLabel
     * @param string  $localeCode
     *
     * @return string
     */
    public function getFlag($code, $fullLabel = false, $localeCode = null)
    {
        $localeCode = $localeCode ?: $this->getCurrentLocaleCode();

        return sprintf(
            '<span class="flag-language"><i class="flag flag-%s"></i><span class="language">%s</span></span>',
            strtolower(\Locale::getRegion($code)),
            $fullLabel ? $this->getLocaleLabel($code, $localeCode) : \Locale::getPrimaryLanguage($code)
        );
    }
}
