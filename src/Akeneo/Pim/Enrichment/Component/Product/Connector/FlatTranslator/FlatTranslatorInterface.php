<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

interface FlatTranslatorInterface
{
    public const FALLBACK_PATTERN = '[%s]';
    public const COLUMN_CODE_AND_TRANSLATION_SEPARATOR = '--';

    public function translate(array $flatItems, string $locale, string $scope, bool $translateHeaders): array;
}
