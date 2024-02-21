<?php

namespace Akeneo\Tool\Component\Localization;

interface CurrencyTranslatorInterface
{
    public function translate(string $currencyCode, string $locale, string $fallback): string;
}
