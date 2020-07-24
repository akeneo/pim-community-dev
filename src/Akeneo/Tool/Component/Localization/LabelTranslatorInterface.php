<?php

namespace Akeneo\Tool\Component\Localization;

interface LabelTranslatorInterface
{
    public function translate(string $id, string $locale, string $fallback = ''): string;
}
