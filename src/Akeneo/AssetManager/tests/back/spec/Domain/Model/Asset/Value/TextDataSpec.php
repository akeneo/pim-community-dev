<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use PhpSpec\ObjectBehavior;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class TextDataSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['This is a text']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TextData::class);
    }

    public function it_can_be_constructed_through_normalized_data()
    {
        $this->beConstructedThrough('createFromNormalize', ['Hello!']);
        $this->shouldBeAnInstanceOf(TextData::class);
    }

    public function it_cannot_be_constructed_with_something_else_than_a_normalized_string()
    {
        $this->beConstructedThrough('createFromNormalize', [null]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_can_be_constructed_with_a_string()
    {
        $this->beConstructedThrough('fromString', ['Hello!']);
        $this->shouldBeAnInstanceOf(TextData::class);
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    /**
     * @see https://akeneo.atlassian.net/browse/PIM-8294
     */
    public function it_can_contain_the_zero_string()
    {
        $this->beConstructedThrough('fromString', ['0']);
        $this->normalize()->shouldReturn('0');
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('This is a text');
    }
}
