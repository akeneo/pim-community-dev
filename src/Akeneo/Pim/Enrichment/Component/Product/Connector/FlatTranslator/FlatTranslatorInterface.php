<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

interface FlatTranslatorInterface
{
    const FALLBACK_PATTERN = '[%s]';
    const COLUMN_CODE_AND_TRANSLATION_SEPARATOR = '--';

    public function translate(array $flatItems, string $locale, string $scope, bool $translateHeaders): array;

    public function translateHeaders(array $columnCodes, string $locale): array;
}
