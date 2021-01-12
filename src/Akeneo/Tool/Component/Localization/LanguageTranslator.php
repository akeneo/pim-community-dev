<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Languages;

class LanguageTranslator implements LanguageTranslatorInterface
{
    public function translate(string $localeCode, string $locale, string $fallback): string
    {
        $displayLocale = \Locale::getPrimaryLanguage($locale);
        list($language, $country) = explode('_', $localeCode);

        try {
            $translatedLanguage = Languages::getName(
                $language,
                $displayLocale
            );
        } catch (MissingResourceException $e) {
            return $fallback;
        }

        try {
            $country = Countries::getName($country, $displayLocale);
        } catch (MissingResourceException $e) {
            return $fallback;
        }

        return sprintf('%s %s', $translatedLanguage, $country);
    }
}
