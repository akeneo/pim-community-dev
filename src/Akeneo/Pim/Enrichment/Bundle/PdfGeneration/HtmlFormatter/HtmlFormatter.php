<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\HtmlFormatter;

interface HtmlFormatter
{
    public function formatHtml(string $html): string;
}
