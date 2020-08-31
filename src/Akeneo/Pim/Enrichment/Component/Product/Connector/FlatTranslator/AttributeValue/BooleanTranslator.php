<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class BooleanTranslator implements FlatAttributeValueTranslatorInterface
{
    /** @var LabelTranslatorInterface */
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
        $trueLocalized = $this->labelTranslator->translate(
            'pim_common.yes',
            $locale,
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'yes')
        );
        $falseLocalized = $this->labelTranslator->translate(
            'pim_common.no',
            $locale,
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'no')
        );

        return array_map(function ($value) use ($trueLocalized, $falseLocalized) {
            $valueLocalized = $value;
            if ('1' === $value) {
                $valueLocalized = $trueLocalized;
            } elseif ('0' === $value) {
                $valueLocalized = $falseLocalized;
            }

            return $valueLocalized;
        }, $values);
    }
}
