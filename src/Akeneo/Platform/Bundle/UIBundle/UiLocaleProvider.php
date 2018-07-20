<?php

namespace Akeneo\Platform\Bundle\UIBundle;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\Localization\Provider\LocaleProviderInterface;
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
    const MAIN_LOCALE = 'en_US';

    /** @var TranslatorInterface */
    protected $translator;

    /** @var float */
    protected $minPercentage;

    /** @var string[] */
    protected $localeCodes;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param TranslatorInterface       $translator
     * @param LocaleRepositoryInterface $localeRepository
     * @param float                     $minPercentage
     * @param string[]                  $localeCodes
     */
    public function __construct(
        TranslatorInterface $translator,
        LocaleRepositoryInterface $localeRepository,
        $minPercentage,
        array $localeCodes
    ) {
        $this->translator = $translator;
        $this->localeRepository = $localeRepository;
        $this->minPercentage = (float) $minPercentage;
        $this->localeCodes = $localeCodes;
    }

    /**
     * Get the list of available locales for the PIM.
     *
     * @return LocaleInterface[]
     */
    public function getLocales()
    {
        $fallbackLocales = $this->translator->getFallbackLocales();
        $mainProgress = $this->getProgress(self::MAIN_LOCALE);
        $availableCodes = [];

        foreach ($this->localeCodes as $code) {
            if ($this->isAvailableLocale($fallbackLocales, $code, $mainProgress)) {
                $availableCodes[] = $code;
            }
        }

        return $this->localeRepository->findBy(['code' => $availableCodes]);
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
     * @param string $code
     * @param int    $mainProgress
     *
     * @return bool
     */
    protected function isAvailableLocale(array $fallbackLocales, $code, $mainProgress)
    {
        if (in_array($code, $fallbackLocales)) {
            return true;
        }

        if (strpos($code, '_') === false) {
            // Remove locales without region
            return false;
        }

        $progress = $this->getProgress($code);

        return ($progress >= $mainProgress * $this->minPercentage);
    }
}
