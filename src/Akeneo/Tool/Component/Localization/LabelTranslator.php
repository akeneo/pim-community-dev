<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

class LabelTranslator implements LabelTranslatorInterface
{
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function translate(string $id, string $locale, string $fallback = ''): string
    {
        $catalog = $this->translator->getCatalogue($locale);
        if (!$catalog->defines($id)) {
            return $fallback;
        }

        return $this->translator->trans($id, [], null, $locale);
    }
}
