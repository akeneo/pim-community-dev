<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatAttributeValueTranslator;

interface FlatAttributeValueTranslatorInterface
{
    public function supports(string $attributeType, string $columnName): bool;

    public function translate(string $attributeCode, array $properties, array $values, string $locale): array;
}
