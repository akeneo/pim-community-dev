<?php

namespace spec\Akeneo\Component\StorageUtils\Exception;

use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\Product;
use Prophecy\Argument;

class ResourceNotFoundExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $objectClassName = Product::class;

        $this->beConstructedWith($objectClassName);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResourceNotFoundException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(\Exception::class);
    }

    function it_returns_an_exception_message()
    {
        $this->getMessage()->shouldReturn(
            sprintf("Can't find resource of type %s", Product::class)
        );
    }
}
