<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeTranslator;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class BooleanFlatTranslator implements AttributeFlatTranslator
{
    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;

    public function __construct(LabelTranslatorInterface $labelTranslator)
    {
        $this->labelTranslator = $labelTranslator;
    }

    public function support(string $attributeType, string $columnName): bool
    {
        return $attributeType === AttributeTypes::BOOLEAN;
    }

    public function translateValues(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $trueLocalized = $this->labelTranslator->translate('pim_common.yes', $locale, '[yes]');
        $falseLocalized = $this->labelTranslator->translate('pim_common.no', $locale, '[no]');

        $result = [];
        foreach ($values as $valueIndex => $value) {
            $result[$valueIndex] = $value ? $trueLocalized : $falseLocalized;
        }

        return $result;
    }
}
