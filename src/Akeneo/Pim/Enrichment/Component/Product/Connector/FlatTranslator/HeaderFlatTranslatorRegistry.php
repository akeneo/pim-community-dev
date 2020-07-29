<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderFlatTranslator\HeaderFlatTranslatorInterface;

class HeaderFlatTranslatorRegistry
{
    private $translators = [];

    public function addTranslator(HeaderFlatTranslatorInterface $translator): void
    {
        $this->translators[] = $translator;
    }

    public function getTranslator(string $columnName): ?HeaderFlatTranslatorInterface
    {
        foreach ($this->translators as $translator) {
            if ($translator->supports($columnName)) {
                return $translator;
            }
        }

        return null;
    }
}
