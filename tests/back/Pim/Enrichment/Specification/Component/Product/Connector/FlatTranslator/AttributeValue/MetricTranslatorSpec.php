<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\SqlGetUnitTranslations;
use PhpSpec\ObjectBehavior;

class MetricTranslatorSpec extends ObjectBehavior
{
    function let(
        SqlGetUnitTranslations $getUnitTranslations
    ) {
        $this->beConstructedWith($getUnitTranslations);
    }

    function it_only_supports_metric_attributes()
    {
        $this->supports('pim_catalog_metric', 'weight-fr_FR-unit')->shouldReturn(true);
        $this->supports('pim_catalog_metric', 'weight-fr_FR')->shouldReturn(false);
        $this->supports('pim_catalog_multiselect', 'name')->shouldReturn(false);
        $this->supports('something_else', 'name')->shouldReturn(false);
    }

    function it_translates_metric_attribute_values(
        SqlGetUnitTranslations $getUnitTranslations
    ) {
        $getUnitTranslations->byMeasurementFamilyCodeAndLocale('Weight', 'fr_FR')->willReturn([
            'MICROGRAM' => 'Microgramme',
            'MILLIGRAM' => 'Milligramme',
            'GRAM' => 'Gramme',
            'KILOGRAM' => 'Kilogramme',
            'TON' => 'Tonne',
            'GRAIN' => 'Grain',
            'DENIER' => 'Denier',
            'ONCE' => 'Once française',
            'MARC' => 'Marc',
            'LIVRE' => 'Livre française',
            'OUNCE' => 'Once',
            'POUND' => 'Livre',
        ]);

        $this->translate('weight-fr_FR-unit', ['reference_data_name' => 'Weight'], ['MICROGRAM', 'ONCE', 'unknown', 'POUND'], 'fr_FR')
            ->shouldReturn(['Microgramme', 'Once française', '[unknown]', 'Livre']);
    }
}
