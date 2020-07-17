<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

class LabelTranslator implements TranslatorInterface
{
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null, $fallback = '')
    {
        $fallbackLocales = $this->translator->getFallbackLocales();

        try {
            $this->translator->setFallbackLocales([]);
            $translation = $this->translator->trans($id, $parameters, $domain, $locale);
        } finally {
            $this->translator->setFallbackLocales($fallbackLocales);
        }

        if ($id === $translation) {
            $translation = $fallback;
        }

        return $translation;
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $fallbackLocales = $this->translator->getFallbackLocales();

        try {
            $this->translator->setFallbackLocales([]);
            $translation = $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
        } finally {
            $this->translator->setFallbackLocales($fallbackLocales);
        }

        return $translation;
    }

    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale()
    {
        return $this->translator->getLocale();
    }
}
