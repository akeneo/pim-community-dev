<?php

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Event;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use PhpSpec\ObjectBehavior;

class MeasurementFamilyCreatedSpec extends ObjectBehavior
{
    public function it_is_created_with_a_measurement_family_code()
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString('weight');

        $this->beConstructedWith($measurementFamilyCode);

        $this->getMeasurementFamilyCode()->shouldReturn($measurementFamilyCode);
    }
}
