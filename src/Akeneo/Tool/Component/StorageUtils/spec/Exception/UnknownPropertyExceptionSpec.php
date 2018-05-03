<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use PhpSpec\ObjectBehavior;

class UnknownPropertyExceptionSpec extends ObjectBehavior
{
    function it_creates_an_unknown_property_exception()
    {
        $previous = new \Exception();
        $exception = UnknownPropertyException::unknownProperty('property', $previous);

        $this->beConstructedWith(
            'property',
            'Property "property" does not exist.',
            0,
            $previous
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn('property');
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
        $this->getPrevious()->shouldReturn($exception->getPrevious());
    }
}
