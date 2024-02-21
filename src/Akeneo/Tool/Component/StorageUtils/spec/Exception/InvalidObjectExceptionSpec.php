<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use PhpSpec\ObjectBehavior;

class InvalidObjectExceptionSpec extends ObjectBehavior
{
    function it_creates_an_immutable_property_exception()
    {
        $exception = InvalidObjectException::objectExpected('stdClass', 'ProductInterface');

        $this->beConstructedWith(
            'stdClass',
            'ProductInterface',
            'Expects a "ProductInterface", "stdClass" given.',
            0
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getObjectClassName()->shouldReturn($exception->getObjectClassName());
        $this->getExpectedClassName()->shouldReturn($exception->getExpectedClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }
}
