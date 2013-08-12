<?php

namespace Pim\Bundle\ConfigBundle\Helper;

use Pim\Bundle\ConfigBundle\Manager\LocaleManager;
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
 *
 */
class LocaleHelper
{
    /**
     * Array of locales codes and associated translations in the current user locale
     *
     * @var array $locales
     *
     * @static
     */
    protected static $locales = array();

    /**
     * @var \Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected $localeManager;

    /**
     * Constructor
     *
     * @param LocaleManager $localeManager
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * Returns the list of displayed locales with the current user locale
     * and initialized it if needed
     *
     * @return array
     */
    protected function getLocales()
    {
        if (empty(static::$locales)) {
            static::$locales = Locale::getDisplayLocales($this->getUserLocale());
        }

        return static::$locales;
    }

    /**
     * Initialized the locales list (if needed) and get the localized label
     *
     * @param string $code
     *
     * @return string
     */
    public function getLocalizedLabel($code)
    {
        $this->getLocales();
        if (isset(static::$locales[$code])) {
            return static::$locales[$code];
        }

        list($lang) = explode('_', $code);
        if (isset(static::$locales[$lang])) {
            return static::$locales[$lang];
        }

        return $code;
    }

    /**
     * Returns the current user locale
     *
     * @return string
     */
    public function getUserLocale()
    {
        return $this->localeManager->getUserLocaleCode();
    }
}
