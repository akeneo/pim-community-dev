<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

interface PropertyFlatTranslatorInterface
{
    public function support(string $columnName): bool;
    public function translateValues(array $values, string $locale): array;
}
