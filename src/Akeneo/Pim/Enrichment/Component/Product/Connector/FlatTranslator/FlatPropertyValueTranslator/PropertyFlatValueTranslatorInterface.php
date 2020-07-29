<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

interface PropertyFlatValueTranslatorInterface
{
    public function supports(string $columnName): bool;
    public function translate(array $values, string $locale, string $scope): array;
}
