<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Exception\MissingResourceException;

class CurrencyTranslator implements CurrencyTranslatorInterface
{
    public function translate(string $currencyCode, string $locale, string $fallback): string
    {
        try {
            $language = \Locale::getPrimaryLanguage($locale);
            $currencyTranslated = Currencies::getName($currencyCode, $language);
        } catch (MissingResourceException $e) {
            return $fallback;
        }

        return $currencyTranslated;
    }
}
