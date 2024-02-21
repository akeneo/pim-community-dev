<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PhpSpec\ObjectBehavior;

class UnitCodeSpec extends ObjectBehavior
{
    private const UNIT_CODE = 'meter';

    function let()
    {
        $this->beConstructedThrough('fromString', [self::UNIT_CODE]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UnitCode::class);
    }

    function it_is_normalizable()
    {
        $this->normalize()->shouldReturn(self::UNIT_CODE);
    }

    function it_is_comparable()
    {
        $this->equals(UnitCode::fromString(self::UNIT_CODE))->shouldBe(true);
        $this->equals(UnitCode::fromString('centimeter'))->shouldBe(false);
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
