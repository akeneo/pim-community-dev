<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatAttributeValueTranslator;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class BooleanFlatTranslator implements FlatAttributeValueTranslatorInterface
{
    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;

    public function __construct(LabelTranslatorInterface $labelTranslator)
    {
        $this->labelTranslator = $labelTranslator;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return $attributeType === AttributeTypes::BOOLEAN;
    }

    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $trueLocalized = $this->labelTranslator->translate('pim_common.yes', $locale, '[yes]');
        $falseLocalized = $this->labelTranslator->translate('pim_common.no', $locale, '[no]');

        return array_map(function ($value) use ($trueLocalized, $falseLocalized) {
            $valueLocalized = $value;
            if ($value === '1') {
                $valueLocalized = $trueLocalized;
            } elseif ($value === '0') {
                $valueLocalized = $falseLocalized;
            }

            return $valueLocalized;
        }, $values);
    }
}
