<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementFamilyCode;
use PhpSpec\ObjectBehavior;

class MeasurementFamilyCodeSpec extends ObjectBehavior
{
    function it_can_be_instantiated()
    {
        $this->beConstructedThrough('fromString', ['duration']);
        $this->shouldHaveType(MeasurementFamilyCode::class);
    }

    function it_throws_an_error_when_code_is_empty()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_displayed_as_a_string()
    {
        $this->beConstructedThrough('fromString', ['duration']);
        $this->asString()->shouldBe('duration');
    }

    function it_equals_to_another_measurement_family_code()
    {
        $this->beConstructedThrough('fromString', ['duration']);
        $this->equals(MeasurementFamilyCode::fromString('duration'))->shouldReturn(true);
        $this->equals(MeasurementFamilyCode::fromString('DURation'))->shouldReturn(true);
        $this->equals(MeasurementFamilyCode::fromString('size'))->shouldReturn(false);
    }
}
