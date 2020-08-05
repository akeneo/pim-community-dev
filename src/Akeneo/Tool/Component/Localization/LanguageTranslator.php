<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Intl\Intl;

class LanguageTranslator implements LanguageTranslatorInterface
{
    public function translate(string $localeCode, string $locale, string $fallback): string
    {
        $displayLocale = \Locale::getPrimaryLanguage($locale);
        list($language, $region) = explode('_', $localeCode);

        $languageTranslated = Intl::getLanguageBundle()->getLanguageName(
            $language,
            $region,
            $displayLocale
        );

        if ($languageTranslated === null) {
            return $fallback;
        }

        return ucfirst($languageTranslated);
    }
}
