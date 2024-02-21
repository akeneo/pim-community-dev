<?php

namespace Akeneo\Tool\Component\Localization;

interface LanguageTranslatorInterface
{
    public function translate(string $localeCode, string $locale, string $fallback): string;
}
