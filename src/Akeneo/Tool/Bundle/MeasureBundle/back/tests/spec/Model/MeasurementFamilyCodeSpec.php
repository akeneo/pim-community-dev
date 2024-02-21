<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use PhpSpec\ObjectBehavior;

class MeasurementFamilyCodeSpec extends ObjectBehavior
{
    private const MEASUREMENT_FAMILY_CODE = 'area';

    function let()
    {
        $this->beConstructedThrough('fromString', [self::MEASUREMENT_FAMILY_CODE]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementFamilyCode::class);
    }

    function it_is_normalizable()
    {
        $this->normalize()->shouldReturn(self::MEASUREMENT_FAMILY_CODE);
    }

    function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('fromString', ['']);
    }

    public function it_should_contain_only_letters_numbers_and_underscores()
    {
        $this->beConstructedThrough('fromString', ['badId!']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', [str_repeat('a', 256)]);
    }
}
