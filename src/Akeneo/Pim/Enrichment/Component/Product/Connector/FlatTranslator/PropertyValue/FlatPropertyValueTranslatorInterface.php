<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

interface FlatPropertyValueTranslatorInterface
{
    public function supports(string $columnName): bool;

    public function translate(array $values, string $locale, string $scope): array;
}
