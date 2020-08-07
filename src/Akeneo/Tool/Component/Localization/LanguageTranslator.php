<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Intl\Intl;

class LanguageTranslator implements LanguageTranslatorInterface
{
    public function translate(string $localeCode, string $locale, string $fallback): string
    {
        $displayLocale = \Locale::getPrimaryLanguage($locale);
        list($language, $region) = explode('_', $localeCode);

        $translatedLanguage = Intl::getLanguageBundle()->getLanguageName(
            $language,
            $region,
            $displayLocale
        );

        if (null === $translatedLanguage) {
            return $fallback;
        }

        return ucfirst($translatedLanguage);
    }
}
