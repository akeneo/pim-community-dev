<?php

namespace spec\Pim\Component\Catalog\EntityWithFamily\Event;

use Pim\Component\Catalog\EntityWithFamily\Event\ParentWasAddedToProduct;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\Event;

class ParentWasAddedToProductSpec extends ObjectBehavior
{
    function let(VariantProductInterface $variantProduct)
    {
        $this->beConstructedWith($variantProduct, 'code');
    }

    function it is initializable()
    {
        $this->shouldHaveType(ParentWasAddedToProduct::class);
    }

    function it an event()
    {
        $this->shouldHaveType(Event::class);
    }

    function it has a product id($variantProduct)
    {
        $this->turnedProduct()->shouldReturn($variantProduct);
    }

    function it has a variant product id()
    {
        $this->parentCode()->shouldReturn('code');
    }
}
