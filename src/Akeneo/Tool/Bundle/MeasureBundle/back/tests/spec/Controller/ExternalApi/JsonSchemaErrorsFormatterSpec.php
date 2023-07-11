<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchemaErrorsFormatter;
use PhpSpec\ObjectBehavior;

class JsonSchemaErrorsFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(JsonSchemaErrorsFormatter::class);
    }

    function it_maps_only_mandatory_properties()
    {
        $errors = [
            [
                'property' => '/property1',
                'message' => 'wrong type',
                'additionalProperty' => 'some additional error description',
            ],
        ];

        $formattedErrors = $this::format($errors);
        $formattedErrors->shouldBeArray();
        $formattedErrors->shouldHaveCount(1);
        $formattedErrors[0]->shouldHaveKey('property');
        $formattedErrors[0]->shouldHaveKey('message');
        $formattedErrors[0]->shouldNotHaveKey('additionalProperty');
    }

    function it_maps_properties_with_default_values()
    {
        $errors = [
            [
                'property' => '/property1',
            ],
            [
                'message' => 'wrong type',
            ]
        ];

        $formattedErrors = $this::format($errors);
        $formattedErrors->shouldBeArray();
        $formattedErrors->shouldHaveCount(2);
        $formattedErrors[0]->shouldHaveKeyWithValue('property', 'property1');
        $formattedErrors[0]->shouldHaveKeyWithValue('message', '');
        $formattedErrors[1]->shouldHaveKeyWithValue('property', '');
        $formattedErrors[1]->shouldHaveKeyWithValue('message', 'wrong type');
    }

    function it_converts_opis_property_paths()
    {
        $errors = [
            [
                'property' => '/property1/property2/1/property3',
            ]
        ];

        $formattedErrors = $this::format($errors);
        $formattedErrors[0]->shouldHaveKeyWithValue('property', 'property1.property2[1].property3');
    }
}
