<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

interface PropertyFlatValueTranslatorInterface
{
    public function support(string $columnName): bool;
    public function translateValues(array $values, string $locale, string $scope): array;
}
