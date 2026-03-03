<?php

declare(strict_types=1);

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyCommonStructureValidator;
use PhpSpec\ObjectBehavior;

class MeasurementFamilyCommonStructureValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementFamilyCommonStructureValidator::class);
    }

    function it_returns_all_the_errors_of_invalid_measurement_family_properties()
    {
        $measurement = [
            'values' => null,
            'foo' => 'bar',
        ];

        $errors = $this->validate($measurement);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_empty_array_if_all_the_required_measurement_family_properties_are_valid()
    {
        $measurementFamily = [
            'code' => 'custom_metric_1',
        ];

        $this->validate($measurementFamily)->shouldReturn([]);
    }
}
