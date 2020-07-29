<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatAttributeValueTranslator\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Structure\Component\Model\Attribute;

class FlatAttributeValueTranslatorRegistry
{
    /**
     * @var FlatAttributeValueTranslatorInterface[]
     */
    private $translators = [];

    /**
     * @var AttributeColumnsResolver
     */
    private $attributeColumnsResolver;

    /**
     * @var AttributeColumnInfoExtractor
     */
    private $attributeColumnInfoExtractor;

    public function __construct(
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor
    ) {
        $this->attributeColumnsResolver = $attributeColumnsResolver;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
    }

    public function addTranslator(FlatAttributeValueTranslatorInterface $translator): void
    {
        $this->translators[] = $translator;
    }

    public function supports(string $columnName)
    {
        return $this->getTranslator($columnName) instanceof FlatAttributeValueTranslatorInterface;
    }

    public function translate(string $columnName, array $values, $locale): array
    {
        $translator = $this->getTranslator($columnName);

        $attribute = $this->getAttribute($columnName);
        $attributeCode = $attribute->getCode();
        $properties = $attribute->getProperties();

        return $translator->translate($attributeCode, $properties, $values, $locale);
    }

    private function getTranslator(string $columnName): ?FlatAttributeValueTranslatorInterface
    {
        if (!$this->isAttributeColumn($columnName)) {
            return null;
        }

        $attribute = $this->getAttribute($columnName);
        $attributeType = $attribute->getType();

        foreach ($this->translators as $translator) {
            if ($translator->supports($attributeType, $columnName)) {
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
