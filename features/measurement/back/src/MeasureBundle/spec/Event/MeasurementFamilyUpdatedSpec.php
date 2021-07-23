<?php

namespace spec\AkeneoMeasureBundle\Event;

use AkeneoMeasureBundle\Model\MeasurementFamilyCode;
use PhpSpec\ObjectBehavior;

class MeasurementFamilyUpdatedSpec extends ObjectBehavior
{
    public function it_is_created_with_a_measurement_family_code()
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString('weight');

        $this->beConstructedWith($measurementFamilyCode);

        $this->getMeasurementFamilyCode()->shouldReturn($measurementFamilyCode);
    }
}
