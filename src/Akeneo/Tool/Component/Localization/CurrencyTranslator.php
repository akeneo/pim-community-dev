<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Intl\Intl;

class CurrencyTranslator implements CurrencyTranslatorInterface
{
    public function translate(string $currencyCode, string $locale, string $fallback): string
    {
        $language = \Locale::getPrimaryLanguage($locale);

        $currencyTranslated = Intl::getCurrencyBundle()->getCurrencyName($currencyCode, $language);
        if ($currencyTranslated === null) {
            return $fallback;
        }

        return $currencyTranslated;
    }
}
