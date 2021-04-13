<?php

namespace Akeneo\Pim\TailoredExport\Connector\Processor\ValueSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;

class MetricValueSelector implements ValueSelectorInterface
{
    private GetUnitTranslations $getUnitTranslations;

    public function __construct(GetUnitTranslations $getUnitTranslations)
    {
        $this->getUnitTranslations = $getUnitTranslations;
    }

    /**
     * @var MetricValueInterface $data
     */
    public function applySelection(array $selection, Attribute $attribute, ValueInterface $data): string
    {
        $selectedValue = null;
        switch ($selection['type']) {
            case 'unit_code':
                $selectedValue = $data->getUnit();
                break;
            case 'amount':
                $selectedValue = $data->getAmount() ?? '';
                break;
            case 'unit_label':
                if ($data->getUnit() === null) {
                    return '';
                }

                $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
                    $attribute->metricFamily(),
                    $selection['locale']
                );

                $selectedValue = $unitTranslations[$data->getUnit()];
                break;
        }

        return $selectedValue ?? '';
    }

    public function support(array $selection, Attribute $attribute)
    {
        return in_array($selection['type'], ['unit_code', 'unit_label', 'amount']) && $attribute->type() === "pim_catalog_metric";
    }
}
