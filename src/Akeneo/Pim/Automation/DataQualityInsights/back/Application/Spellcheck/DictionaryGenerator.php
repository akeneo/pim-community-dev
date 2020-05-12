<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\DictionarySource;

interface DictionaryGenerator
{
    public function generate(DictionarySource $dictionarySource): void;
}
