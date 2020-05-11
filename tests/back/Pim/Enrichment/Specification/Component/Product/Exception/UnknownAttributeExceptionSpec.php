<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use PhpSpec\ObjectBehavior;

class UnknownAttributeExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('unknownAttribute', ['attribute_code']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UnknownAttributeException::class);
    }

    function it_is_a_property_exception()
    {
        $this->shouldHaveType(PropertyException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(\Exception::class);
    }

    function it_returns_an_exception_message()
    {
        $this->getMessage()->shouldReturn(sprintf(
            'Attribute "%s" does not exist.',
            'attribute_code'
        ));
    }

    function it_returns_a_property_name()
    {
        $this->getPropertyName()->shouldReturn('attribute_code');
    }

    function it_returns_the_previous_exception()
    {
        $previous = new \Exception();
        $this->beConstructedThrough('unknownAttribute', ['attribute_code', $previous]);

        $this->getPrevious()->shouldReturn($previous);
    }
}
