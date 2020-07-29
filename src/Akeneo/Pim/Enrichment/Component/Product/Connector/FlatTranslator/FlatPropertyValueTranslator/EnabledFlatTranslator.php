<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class EnabledFlatTranslator implements PropertyFlatTranslatorInterface
{
    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;

    public function __construct(LabelTranslatorInterface $labelTranslator)
    {
        $this->labelTranslator = $labelTranslator;
    }

    public function support(string $columnName): bool
    {
        return $columnName === 'enabled';
    }

    public function translateValues(array $values, string $locale): array
    {
        $result = [];
        $trueLocalized = $this->labelTranslator->translate('pim_common.yes', $locale, '[yes]');
        $falseLocalized = $this->labelTranslator->translate('pim_common.no', $locale, '[no]');

        foreach ($values as $valueIndex => $value) {
            $result[$valueIndex] = $value ? $trueLocalized : $falseLocalized;
        }

        return $result;
    }
}
