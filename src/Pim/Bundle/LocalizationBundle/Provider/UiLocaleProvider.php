<?php

namespace Pim\Bundle\LocalizationBundle\Provider;

use Akeneo\Component\Localization\Provider\LocaleProviderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * The LocaleProvider gets the list of available locales for the PIM. A locale is available when it is translated
 * to more than a defined percentage of the main locale.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UiLocaleProvider implements LocaleProviderInterface
{
    const MAIN_LOCALE = 'en';

    /** @var TranslatorInterface */
    protected $translator;

    /** @var float */
    protected $minPercentage;

    /**
     * @param TranslatorInterface $translator
     * @param float               $minPercentage
     */
    public function __construct(TranslatorInterface $translator, $minPercentage)
    {
        $this->translator = $translator;
        $this->minPercentage = (float) $minPercentage;
    }

    /**
     * Get the list of available locales for the PIM.
     *
     * @return array
     */
    public function getLocales()
    {
        $locales = [];

        $fallbackLocales = $this->translator->getFallbackLocales();
        $localeNames = Intl::getLocaleBundle()->getLocaleNames(self::MAIN_LOCALE);
        $mainProgress = $this->getProgress(self::MAIN_LOCALE);

        foreach ($localeNames as $code => $locale) {
            if ($this->isAvailableLocale($fallbackLocales, $locales, $code, $mainProgress)) {
                $locales[$code] = $locale;
            }
        }

        return $locales;
    }

    /**
     * Return the number of translated messages
     *
     * @param string $locale
     *
     * @return int
     */
    protected function getProgress($locale)
    {
        $catalogue = $this->translator->getCatalogue($locale);

        return count($catalogue->all(), COUNT_RECURSIVE);
    }

    /**
     * Return if the locale is available. A locale is available if it belongs to the fallback locales or if it is
     * translated to more than the percentage of the main locale.
     *
     * @param array  $fallbackLocales
     * @param array  $locales
     * @param string $code
     * @param int    $mainProgress
     *
     * @return bool
     */
    protected function isAvailableLocale(array $fallbackLocales, array $locales, $code, $mainProgress)
    {
        if (in_array($code, $fallbackLocales)) {
            return true;
        }

        if (strlen($code) > 2 && isset($locales[substr($code, 0, 2)])) {
            return true;
        }
        $progress = $this->getProgress($code);

        return ($progress >= $mainProgress * $this->minPercentage);
    }
}
