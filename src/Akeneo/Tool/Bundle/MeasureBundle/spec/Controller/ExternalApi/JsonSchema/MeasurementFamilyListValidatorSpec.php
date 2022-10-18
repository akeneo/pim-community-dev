<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyListValidator;
use PhpSpec\ObjectBehavior;

class MeasurementFamilyListValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementFamilyListValidator::class);
    }

    function it_returns_the_errors_of_an_invalid_list_of_measurement_families()
    {
        $measurementFamilyList = [['not a object'], 'not an array'];

        $errors = $this->validate($measurementFamilyList);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    function it_returns_an_empty_array_if_the_list_of_measurement_families_is_valid()
    {
        $measurementFamilyList = [
            [
                'code' => 'kilogram',
            ],
            [
                'code' => 'dyson',
            ]
        ];

        $this->validate($measurementFamilyList)->shouldBe([]);
    }
}
