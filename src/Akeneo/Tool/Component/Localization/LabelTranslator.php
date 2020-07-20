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
        $catalog = $this->translator->getCatalogue($locale);
        if ($catalog->defines($id)) {
            return $fallback;
        }

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
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
