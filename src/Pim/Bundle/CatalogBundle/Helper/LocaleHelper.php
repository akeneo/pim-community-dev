<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Symfony\Component\Intl;
use Pim\Bundle\UserBundle\Context\UserContext;
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
    /** @var UserContext */
    protected $userContext;

    /** @var LocaleManager*/
    protected $localeManager;

    /**
     * Constructor
     * @param UserContext   $userContext
     * @param LocaleManager $localeManager
     */
    public function __construct(UserContext $userContext, LocaleManager $localeManager)
    {
        $this->userContext   = $userContext;
        $this->localeManager = $localeManager;
    }

    /**
     * Returns the current locale
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     *
     * @deprecated Locale object providing is not necessary. Use getCurrentLocaleCode instead. (will be removed in 1.3)
     */
    public function getCurrentLocale()
    {
        return $this->userContext->getCurrentLocale();
    }

    /**
     * Returns the current locale code
     *
     * @return string
     */
    public function getCurrentLocaleCode()
    {
        return $this->userContext->getCurrentLocale()->getCode();
    }

    /**
     * Returns the label of a locale in the specified language
     *
     * @param string $code        the code of the locale to translate
     * @param string $translateIn the locale in which the label should be translated (if null, user locale will be used)
     *
     * @return string
     */
    public function getLocaleLabel($code, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();

        return \Locale::getDisplayName($code, $translateIn);
    }

    /**
     * Returns the symbol for a currency
     *
     * @param string $currency
     * @param string $translateIn
     *
     * @return string
     */
    public function getCurrencySymbol($currency, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($translateIn);

        return Intl\Intl::getCurrencyBundle()->getCurrencySymbol($currency, $language);
    }

    /**
     * Returns the label for a currency
     *
     * @param string $currency
     * @param string $translateIn
     *
     * @return string
     */
    public function getCurrencyLabel($currency, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($translateIn);

        return Intl\Intl::getCurrencyBundle()->getCurrencyName($currency, $language);
    }

    /**
     * Returns an array of all known currency names, indexed by code
     *
     * @param string $translateIn
     *
     * @return string
     */
    public function getCurrencyLabels($translateIn = null)
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();
        $language = \Locale::getPrimaryLanguage($translateIn);

        return Intl\Intl::getCurrencyBundle()->getCurrencyNames($language);
    }

    /**
     * Returns the flag icon for a locale
     *
     * @param string  $code
     * @param boolean $fullLabel
     * @param string  $translateIn
     *
     * @return string
     *
     * @deprecated Use the flag twig filter. Will be removed in 1.3
     */
    public function getFlag($code, $fullLabel = false, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->getCurrentLocaleCode();

        return sprintf(
            '<span class="flag-language"><i class="flag flag-%s"></i><span class="language">%s</span></span>',
            strtolower(\Locale::getRegion($code)),
            $fullLabel ? $this->getLocaleLabel($code, $translateIn) : \Locale::getPrimaryLanguage($code)
        );
    }

    /**
     * Get the language from a locale code
     *
     * @param string $code
     *
     * @return string
     */
    public function getLanguage($code)
    {
        return \Locale::getPrimaryLanguage($code);
    }

    /**
     * Get the region from a locale code
     *
     * @param string $code
     *
     * @return string
     */
    public function getRegion($code)
    {
        return \Locale::getRegion($code);
    }

    /**
     * Get activated locales as choices
     *
     * @return string[]
     */
    public function getActivatedLocaleChoices()
    {
        $translateIn  = $this->getCurrentLocaleCode();
        $activeCodes = $this->localeManager->getActiveCodes();

        $results = [];
        foreach ($activeCodes as $activeCode) {
            $results[$activeCode] = $this->getLocaleLabel($activeCode, $translateIn);
        }

        return $results;
    }
}
