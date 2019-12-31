<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

interface DictionaryGenerator
{
    public function generate(DictionarySource $dictionarySource): void;
}
