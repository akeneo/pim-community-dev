<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

class NullFlatTranslator implements FlatTranslatorInterface
{
    public function translate(array $flatItems, string $locale, bool $translateHeaders): array
    {
        return $flatItems;
    }
}
