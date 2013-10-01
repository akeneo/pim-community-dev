<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
     * @var SecurityContextInterface
     */
    protected $securityContext;
    /**
     * @var LocaleManager
     */
    protected $localeManager;
    /**
     * @var string
     */
    protected $defaultLocale;
    /**
     * @var Request
     */
    protected $request;

    public function __construct(LocaleManager $localeManager, SecurityContextInterface $securityContext, $defaultLocale)
    {
        $this->localeManager = $localeManager;
        $this->securityContext = $securityContext;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Sets the current request
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Returns the current locale from the request, or the default locale if no active request is found
     * 
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->request ? $this->request->getLocale() : $this->defaultLocale;
    }

    /**
     * Initialized the locales list (if needed) and get the localized label
     *
     * @param string $code the code of the local's label
     * @param string $locale the locale in which the label should be translated
     * @return string
     */
    public function getLocaleLabel($code, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getCurrentLocale();
        }

        return \Locale::getDisplayName($code, $locale);
    }

    /**
     * Returns the symbol for a currency
     * 
     * @param string $currency
     * @param string $locale
     * @return string
     */
    public function getCurrencySymbol($currency, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getCurrentLocale();
        }
        
        return Intl\Intl::getCurrencyBundle()->getCurrencySymbol($currency, $locale);
    }
    
    /**
     * Returns the label for a currency
     * 
     * @param string $currency
     * @param string $locale
     * @return string
     */
    public function getCurrencyLabel($currency, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getCurrentLocale();
        }

        return Intl\Intl::getCurrencyBundle()->getCurrencyName($currency, $locale);
    }
    
    /**
     * Returns an array of all known currency names, indexed by code
     * 
     * @param string $currency
     * @param string $locale
     * @return string
     */
    public function getCurrencyLabels($locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getCurrentLocale();
        }
        return Intl\Intl::getCurrencyBundle()->getCurrencyNames($locale);
    }

    /**
     * Returns the catalog locale currency
     * 
     * @return string
     */
    public function getLocaleCurrency()
    {
        $localeCode = $this->getCatalogLocale();
        $locale = $this->localeManager->getLocaleByCode($localeCode);

        return (null !== $locale && $locale->getDefaultCurrency()) ? $locale->getDefaultCurrency()->getCode() : null;
    }

    /**
     * Returns the flag icon for a locale
     * 
     * @param string $code
     * @param string $locale
     * @return string
     */
    public function getFlag($code, $fullLabel = false, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getCurrentLocale();
        }
        $localeLabel = $this->getLocaleLabel($code, $locale);
        
        return sprintf(
            '<img src="%s" class="flag flag-%s" alt="%s" /><code class="flag-language">%s</code>',
            '/bundles/pimui/images/blank.gif',
            strtolower(\Locale::getRegion($code)),
            $localeLabel,
            $fullLabel ? $localeLabel : \Locale::getPrimaryLanguage($code)
        );
    }

    /**
     * Returns the locale for the catalog
     * 
     * @return string|NULL
     */
    protected function getCatalogLocale()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return (string) $user->getCatalogLocale();
    }
}
