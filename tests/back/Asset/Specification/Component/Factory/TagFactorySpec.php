<?php

namespace Specification\Akeneo\Asset\Component\Factory;

use Akeneo\Asset\Component\Factory\TagFactory;
use Akeneo\Asset\Component\Model\Tag;
use PhpSpec\ObjectBehavior;

class TagFactorySpec extends ObjectBehavior
{
    const TAG_CLASS = Tag::class;

    function let()
    {
        $this->beConstructedWith(self::TAG_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType(TagFactory::class);
    }

    function it_creates_a_tag()
    {
        $this->create()->shouldReturnAnInstanceOf(self::TAG_CLASS);
    }
}
