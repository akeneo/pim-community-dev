<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Symfony\Component\Locale\Locale;

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
     * Initialized the locales list (if needed) and get the localized label
     *
     * @param string $code
     *
     * @return string
     */
    public function getLocalizedLabel($code, $locale)
    {
        $locales = Locale::getDisplayLocales($locale);

        if (isset($locales[$code])) {
            return $locales[$code];
        }

        list($lang) = explode('_', $code);
        if (isset($locales[$lang])) {
            return $locales[$lang];
        }

        return $code;
    }
}
