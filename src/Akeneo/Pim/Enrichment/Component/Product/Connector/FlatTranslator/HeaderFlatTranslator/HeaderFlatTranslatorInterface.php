<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderFlatTranslator;

interface HeaderFlatTranslatorInterface
{
    public function supports(string $columnName): bool;

    public function translate(string $columnName, string $locale, HeaderTranslationContext $context);
}
