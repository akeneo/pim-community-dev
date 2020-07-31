<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

interface FlatTranslatorInterface
{
    public function translate(array $flatItems, string $locale, string $scope, bool $translateHeaders): array;
}
