<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatAttributeValueTranslator;

interface FlatAttributeValueTranslatorInterface
{
    public function support(string $attributeType, string $columnName): bool;

    public function translateValues(string $attributeCode, array $properties, array $values, string $locale): array;
}
