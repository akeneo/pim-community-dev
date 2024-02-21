<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyValidator;
use PhpSpec\ObjectBehavior;

class MeasurementFamilyValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementFamilyValidator::class);
    }

    function it_returns_all_the_errors_of_invalid_measurement_family_properties()
    {
        $measurement = [
            'values' => null,
            'foo' => 'bar',
            'code' => 1,
            'units' => true,
            'standard_unit_code' => [],
        ];

        $errors = $this->validate($measurement);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(4);
    }

    function it_returns_an_empty_array_if_all_the_measurement_family_properties_are_valid()
    {
        $measurementFamily = [
            'code' => 'custom_metric_1',
            'labels' =>
                [
                    'en_US' => 'Custom measurement 1',
                    'fr_FR' => 'Mesure personalisée 1',
                ],
            'standard_unit_code' => 'CUSTOM_UNIT_1_1',
            'units' =>
                [
                    'CUSTOM_UNIT_1_1' => [
                        'code' => 'CUSTOM_UNIT_1_1',
                        'labels' =>
                            [
                                'en_US' => 'Custom unit 1_1',
                                'fr_FR' => 'Unité personalisée 1_1',
                            ],
                        'convert_from_standard' =>
                            [
                                [
                                    'operator' => 'mul',
                                    'value' => '1',
                                ],
                            ],
                        'symbol' => 'mm²',
                    ],
                    [
                        'code' => 'CUSTOM_UNIT_2_1',
                        'labels' =>
                            [
                                'en_US' => 'Custom unit 2_1',
                                'fr_FR' => 'Unité personalisée 2_1',
                            ],
                        'convert_from_standard' =>
                            [
                                [
                                    'operator' => 'mul',
                                    'value' => '0.0001',
                                ],
                            ],
                        'symbol' => 'cm²',
                    ],
                ],
        ];

        $this->validate($measurementFamily)->shouldReturn([]);
    }

    function it_returns_an_empty_array_if_only_the_required_properties_are_given()
    {
        $measurementFamily = [
            'code' => 'custom_metric_1',
            'standard_unit_code' => 'CUSTOM_UNIT_1_1',
            'units' => [
                'CUSTOM_UNIT_1_1' => [
                    'code' => 'CUSTOM_UNIT_1_1',
                    'convert_from_standard' => [
                        [
                            'operator' => 'mul',
                            'value' => '1',
                        ],
                    ],
                ]
            ],
        ];

        $this->validate($measurementFamily)->shouldReturn([]);
    }
}
