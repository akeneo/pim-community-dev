<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use PhpSpec\ObjectBehavior;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EmptyDataSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', []);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(EmptyData::class);
    }

    public function it_can_be_constructed_through_normalized_data()
    {
        $this->beConstructedThrough('createFromNormalize', [null]);
        $this->shouldBeAnInstanceOf(EmptyData::class);

    }

    public function it_cannot_be_constructed_with_something_else_than_a_normalized_null_value()
    {
        $this->beConstructedThrough('createFromNormalize', ['hello']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_can_be_constructed()
    {
        $this->beConstructedThrough('create', []);
        $this->shouldBeAnInstanceOf(EmptyData::class);
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(null);
    }
}
