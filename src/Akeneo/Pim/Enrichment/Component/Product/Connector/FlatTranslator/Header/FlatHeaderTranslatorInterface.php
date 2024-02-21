<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

interface FlatHeaderTranslatorInterface
{
    public function supports(string $columnName): bool;

    public function warmup(array $columnNames, string $locale): void;

    public function translate(string $columnName, string $locale): string;
}
