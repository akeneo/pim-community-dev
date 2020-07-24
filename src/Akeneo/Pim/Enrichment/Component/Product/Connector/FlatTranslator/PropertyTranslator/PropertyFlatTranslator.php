<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyTranslator;

interface PropertyFlatTranslator
{
    public function support(string $columnName): bool;
    public function translateValues(array $values, string $locale): array;
}
