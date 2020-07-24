<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeFlatTranslator;

interface AttributeFlatTranslator
{
    public function supports(string $columnName);

    public function translateValues(array $values, string $locale);
}
