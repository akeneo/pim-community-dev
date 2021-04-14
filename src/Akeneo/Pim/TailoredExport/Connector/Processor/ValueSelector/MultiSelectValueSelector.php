<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor\ValueSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

class MultiSelectValueSelector implements ValueSelectorInterface
{
    private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues;

    public function __construct(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function applySelection(array $selection, Attribute $attribute, ValueInterface $value): string
    {
        $selectedData = '';

        switch ($selection['type']) {
            case 'code':
                $selectedData = $value->getData();
                break;
            case 'label':
                $optionsKeys = $this->generateOptionKeys($value->getData(), $attribute->code());

                $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
                    $optionsKeys
                );

                $selectedData = array_map(function ($optionCode) use ($attributeOptionTranslations, $attribute, $selection) {
                    return $attributeOptionTranslations[sprintf('%s.%s', $attribute->code(), $optionCode)][$selection['locale']] ?? $optionCode;
                }, $value->getData());
                break;
        }

        return implode(', ', $selectedData);
    }

    public function support(array $selection, Attribute $attribute)
    {
        return $attribute->type() === AttributeTypes::OPTION_MULTI_SELECT;
    }

    private function generateOptionKeys(array $data, string $attributeCode): array
    {
        return array_map(
            function ($optionCode) use ($attributeCode) {
                return sprintf('%s.%s', $attributeCode, $optionCode);
            },
            $data
        );
    }
}
