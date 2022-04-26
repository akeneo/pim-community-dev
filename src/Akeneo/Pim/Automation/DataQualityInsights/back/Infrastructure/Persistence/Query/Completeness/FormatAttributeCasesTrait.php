<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

trait FormatAttributeCasesTrait
{
    /**
     * @param AttributeCase[] $attributeCases
     */
    private function formatAttributeCases(iterable $attributeCases): string
    {
        $formattedCases = [];
        foreach ($attributeCases as $attributeCase) {
            $formattedCases[] = $attributeCase->addCases();
        }
        return implode(' ', $formattedCases);
    }
}
