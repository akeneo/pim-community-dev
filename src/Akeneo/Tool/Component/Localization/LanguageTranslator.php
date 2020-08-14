<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Intl;

class LanguageTranslator implements LanguageTranslatorInterface
{
    public function translate(string $localeCode, string $locale, string $fallback): string
    {
        $displayLocale = \Locale::getPrimaryLanguage($locale);
        list($language, $country) = explode('_', $localeCode);

        $translatedLanguage = Intl::getLanguageBundle()->getLanguageName(
            $language,
            $country,
            $displayLocale
        );

        if (null === $translatedLanguage) {
            return $fallback;
        }

        $country = Countries::getName($country, $displayLocale);

        return sprintf('%s %s', $translatedLanguage, $country);
    }
}
