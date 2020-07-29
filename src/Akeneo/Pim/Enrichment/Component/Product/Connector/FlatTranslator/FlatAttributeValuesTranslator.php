<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Structure\Component\Model\Attribute;

class FlatAttributeValuesTranslator
{
    /**
     * @var FlatAttributeValueTranslatorRegistry
     */
    private $flatAttributeValueTranslatorRegistry;

    /**
     * @var AttributeColumnInfoExtractor
     */
    private $attributeColumnInfoExtractor;
    /**
     * @var AttributeColumnsResolver
     */
    private $attributeColumnsResolver;

    public function __construct(
        FlatAttributeValueTranslatorRegistry $flatAttributeValueTranslatorRegistry,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        AttributeColumnsResolver $attributeColumnsResolver
    ) {
        $this->flatAttributeValueTranslatorRegistry = $flatAttributeValueTranslatorRegistry;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->attributeColumnsResolver = $attributeColumnsResolver;
    }

    public function supports($columnName)
    {
        return in_array($columnName, $this->attributeColumnsResolver->resolveAttributeColumns());
    }

    public function translate(string $columnName, array $values, string $locale)
    {
        $attribute = $this->getAttributeFromColumnName($columnName);
        $attributeType = $attribute->getType();
        $attributeCode = $attribute->getCode();
        $attributeProperties = $attribute->getProperties();

        $translator = $this->flatAttributeValueTranslatorRegistry->getTranslator($attributeType, $columnName);
        if ($translator === null) {
            return $values;
        }

        return $translator->translate($attributeCode, $attributeProperties, $values, $locale);
    }

    private function getAttributeFromColumnName(string $columnName): Attribute
    {
        $columnInformations = $this->attributeColumnInfoExtractor->extractColumnInfo($columnName);

        return $columnInformations['attribute'];
    }
}
