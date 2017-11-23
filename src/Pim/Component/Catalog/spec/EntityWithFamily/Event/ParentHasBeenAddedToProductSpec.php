<?php

namespace spec\Pim\Component\Catalog\EntityWithFamily\Event;

use Pim\Component\Catalog\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\Event;

class ParentHasBeenAddedToProductSpec extends ObjectBehavior
{
    function let(VariantProductInterface $variantProduct)
    {
        $this->beConstructedWith($variantProduct, 'code');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParentHasBeenAddedToProduct::class);
    }

    function it_an_event()
    {
        $this->shouldHaveType(Event::class);
    }

    function it_has_a_product_id($variantProduct)
    {
        $this->convertedProduct()->shouldReturn($variantProduct);
    }

    function it_has_a_variant_product_id()
    {
        $this->parentCode()->shouldReturn('code');
    }
}
