<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use PhpSpec\ObjectBehavior;

class QualityScoreConverterSpec extends ObjectBehavior
{

    function it_converts_quality_score_value_attributes_from_standard_to_flat_format()
    {

        $data = [
            "ecommerce" => [
                'en_US' => "B",
                'fr_FR' => "C"
            ]
        ];

        $converterResult = [
            sprintf('%s-en_US-ecommerce', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX) => 'B',
            sprintf('%s-fr_FR-ecommerce', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX) => 'C'
        ];

        $this->convert($data)->shouldReturn($converterResult);

    }
}
