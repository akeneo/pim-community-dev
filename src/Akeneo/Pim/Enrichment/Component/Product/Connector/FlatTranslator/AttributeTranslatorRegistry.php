<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeTranslator\AttributeFlatTranslator;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Webmozart\Assert\Assert;

class AttributeTranslatorRegistry
{
    /**
     * @var AttributeFlatTranslator[]
     */
    private $translators;

    /**
     * @var AttributeColumnsResolver
     */
    private $attributeColumnsResolver;

    /**
     * @var AttributeColumnInfoExtractor
     */
    private $attributeColumnInfoExtractor;

    /**
     * AttributeTranslatorRegistry constructor.
     * @param AttributeFlatTranslator[] $translators
     * @param AttributeColumnsResolver $attributeColumnsResolver
     * @param AttributeColumnInfoExtractor $attributeColumnInfoExtractor
     */
    public function __construct(
        array $translators,
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor
    ) {
        Assert::allImplementsInterface($translators, AttributeFlatTranslator::class);
        $this->translators = $translators;
        $this->attributeColumnsResolver = $attributeColumnsResolver;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
    }

    public function support(string $columnName)
    {
        return $this->getTranslator($columnName) instanceof AttributeFlatTranslator;
    }

    public function translate(string $columnName, array $values, $locale): array
    {
        $translator = $this->getTranslator($columnName);

        $attribute = $this->getAttribute($columnName);
        $attributeCode = $attribute->getCode();
        $properties = $attribute->getProperties();

        return $translator->translateValues($attributeCode, $properties, $values, $locale);
    }

    private function getTranslator(string $columnName): ?AttributeFlatTranslator
    {
        if (!$this->isAttributeColumn($columnName)) {
            return null;
        }

        $attribute = $this->getAttribute($columnName);
        $attributeType = $attribute->getType();

        foreach ($this->translators as $translator) {
            if ($translator->support($attributeType, $columnName)) {
                return $translator;
            }
        }

        return null;
    }

    private function getAttribute(string $columnName): Attribute
    {
        $columnInformations = $this->attributeColumnInfoExtractor->extractColumnInfo($columnName);

        return $columnInformations['attribute'];
    }

    private function isAttributeColumn(string $columnName)
    {
        $attributeColumns = $this->attributeColumnsResolver->resolveAttributeColumns();

        return in_array($columnName, $attributeColumns);
    }
}
