<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;

class FindAttributeOptionLabels implements FindAttributeOptionLabelsInterface
{
    private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues;

    public function __construct(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function byAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes, string $locale): array
    {
        $optionKeys = array_map(function ($optionCode) use ($attributeCode) {
            return sprintf('%s.%s', $attributeCode, $optionCode);
        }, $optionCodes);

        $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues
            ->fromAttributeCodeAndOptionCodes($optionKeys);

        return array_reduce($optionCodes, function (&$carry, $optionCode) use ($attributeCode, $attributeOptionTranslations, $locale) {
            $optionKey = sprintf('%s.%s', $attributeCode, $optionCode);

            $carry[$optionKey] = $attributeOptionTranslations[$optionKey][$locale] ?? null;

            return $carry;
        }, []);
    }
}
