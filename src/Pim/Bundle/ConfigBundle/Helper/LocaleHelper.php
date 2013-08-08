<?php

namespace Pim\Bundle\ConfigBundle\Helper;

use Pim\Bundle\ConfigBundle\Manager\LocaleManager;
use Symfony\Component\Locale\Locale;

/**
 * LocaleHelper essentially allow to translate locale code to localized locale label
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
     * Constructor defining the locales and associated translations
     * from the current user locale
     *
     * @param LocaleManager $localeManager
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
        self::$locales = Locale::getDisplayLocales($this->getUserLocale());
    }

    /**
     *
     * @param unknown_type $code
     * @return Ambigous <>|unknown
     */
    public function getLocalizedLabel($code)
    {
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
    protected function getUserLocale()
    {
        return $this->localeManager->getUserLocaleCode();
    }

    /**
     *
     * @param array $localesArray of ChoiceView
     * @return number|unknown
     *
     * TODO : Maybe create a ChoiceViewHelper to sort the values
     */
    public function reorderLocales(array $locales)
    {
        uasort(
            $locales,
            function ($a, $b) {
                if ($a->label == $b->label) {
                    return 0;
                }

                return ($a->label < $b->label) ? -1 : 1;
            }
        );

        return $locales;
    }
}
