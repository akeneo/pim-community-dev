<?php

namespace spec\Akeneo\Component\Buffer;

use PhpSpec\ObjectBehavior;

class BufferFactorySpec extends ObjectBehavior
{
    const ARRAY_BUFFER_CLASS = 'Akeneo\Component\Buffer\ArrayBuffer';

    function let()
    {
        $this->beConstructedWith(self::ARRAY_BUFFER_CLASS);
    }

    function it_throws_an_exception_if_configured_with_a_wrong_classname()
    {
        $this
            ->shouldThrow('Akeneo\Component\Buffer\Exception\InvalidClassNameException')
            ->during('__construct', ['\\stdClass']);
    }

    function it_creates_a_buffer()
    {
        $this->create()->shouldReturnAnInstanceOf(self::ARRAY_BUFFER_CLASS);
    }
}
