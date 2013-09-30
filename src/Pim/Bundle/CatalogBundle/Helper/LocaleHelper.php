<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Symfony\Component\HttpFoundation\Request;

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
    private $defaultLocale;
    private $request;
    
    public function __construct($defaultLocale)
    {
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
    public function getLocalizedLabel($code, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getCurrentLocale();
        }

        return \Locale::getDisplayName($code, $locale);
    }
}
