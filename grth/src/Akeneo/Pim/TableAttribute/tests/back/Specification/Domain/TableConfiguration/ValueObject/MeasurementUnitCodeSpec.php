<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementUnitCode;
use PhpSpec\ObjectBehavior;

class MeasurementUnitCodeSpec extends ObjectBehavior
{
    function it_can_be_instantiated()
    {
        $this->beConstructedThrough('fromString', ['second']);
        $this->shouldHaveType(MeasurementUnitCode::class);
    }

    function it_throws_an_error_when_code_is_empty()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_displayed_as_a_string()
    {
        $this->beConstructedThrough('fromString', ['second']);
        $this->asString()->shouldBe('second');
    }
}
