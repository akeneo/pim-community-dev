<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Intl\Currencies;

class CurrencyTranslator implements CurrencyTranslatorInterface
{
    public function translate(string $currencyCode, string $locale, string $fallback): string
    {
        $language = \Locale::getPrimaryLanguage($locale);

        $currencyTranslated = Currencies::getName($currencyCode, $language);
        if (null === $currencyTranslated) {
            return $fallback;
        }

        return $currencyTranslated;
    }
}
