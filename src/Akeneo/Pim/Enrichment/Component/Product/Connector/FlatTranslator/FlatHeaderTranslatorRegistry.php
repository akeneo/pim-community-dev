<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatHeaderTranslator\FlatHeaderTranslatorInterface;

class FlatHeaderTranslatorRegistry
{
    private $translators = [];

    public function addTranslator(FlatHeaderTranslatorInterface $translator): void
    {
        $this->translators[] = $translator;
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
