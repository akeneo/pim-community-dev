<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\EventDispatcher\Event;

class ParentHasBeenAddedToProductSpec extends ObjectBehavior
{
    function let(ProductInterface $variantProduct)
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
