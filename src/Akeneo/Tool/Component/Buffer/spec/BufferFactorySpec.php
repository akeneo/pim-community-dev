<?php

namespace spec\Akeneo\Tool\Component\Buffer;

use Akeneo\Tool\Component\Buffer\Exception\InvalidClassNameException;
use Akeneo\Tool\Component\Buffer\JSONFileBuffer;
use PhpSpec\ObjectBehavior;

class BufferFactorySpec extends ObjectBehavior
{
    const JSON_BUFFER_CLASS = JSONFileBuffer::class;

    function let()
    {
        $this->beConstructedWith(self::JSON_BUFFER_CLASS);
    }

    function it_throws_an_exception_if_configured_with_a_wrong_classname()
    {
        $this
            ->shouldThrow(InvalidClassNameException::class)
            ->during('__construct', ['\\stdClass']);
    }

    function it_creates_a_buffer()
    {
        $this->create()->shouldReturnAnInstanceOf(self::JSON_BUFFER_CLASS);
    }
}
