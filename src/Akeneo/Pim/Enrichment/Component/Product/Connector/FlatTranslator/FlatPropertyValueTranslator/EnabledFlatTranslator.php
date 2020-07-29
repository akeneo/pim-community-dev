<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class EnabledFlatTranslator implements PropertyFlatValueTranslatorInterface
{
    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;

    public function __construct(LabelTranslatorInterface $labelTranslator)
    {
        $this->labelTranslator = $labelTranslator;
    }

    public function supports(string $columnName): bool
    {
        return $columnName === 'enabled';
    }

    public function translate(array $values, string $locale, string $scope): array
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
