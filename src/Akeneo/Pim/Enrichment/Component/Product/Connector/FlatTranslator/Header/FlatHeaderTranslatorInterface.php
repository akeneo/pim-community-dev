<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

interface FlatHeaderTranslatorInterface
{
    public function supports(string $columnName): bool;

    public function warmup(string $columnName, string $locale);

    public function translate(string $columnName, string $locale);
}
