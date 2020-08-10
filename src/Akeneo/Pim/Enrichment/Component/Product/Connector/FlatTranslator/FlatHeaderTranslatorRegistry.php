<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\FlatHeaderTranslatorInterface;

class FlatHeaderTranslatorRegistry
{
    private $translators = [];

    public function addTranslator(FlatHeaderTranslatorInterface $translator): void
    {
        $this->translators[] = $translator;
    }

    public function warmup(array $columnNames, string $locale)
    {
        foreach ($this->translators as $translator) {
            $translator->warmup($columnNames, $locale);
        }
    }

    public function getTranslator(string $columnName): ?FlatHeaderTranslatorInterface
    {
        foreach ($this->translators as $translator) {
            if ($translator->supports($columnName)) {
                return $translator;
            }
        }

        return null;
    }
}
