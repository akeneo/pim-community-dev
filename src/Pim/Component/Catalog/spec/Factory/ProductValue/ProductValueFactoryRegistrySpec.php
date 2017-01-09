<?php

namespace spec\Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface;
use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductValueFactoryRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueFactoryRegistry::class);
    }

    function it_registers_a_factory(ProductValueFactoryInterface $factory)
    {
        $this->register($factory);
    }

    function it_gets_a_registered_factory(
        ProductValueFactoryInterface $factory1,
        ProductValueFactoryInterface $factory2
    ) {
        $this->register($factory1);
        $this->register($factory2);

        $factory1->supports('text')->willReturn(false);
        $factory2->supports('text')->willReturn(true);

        $this->get('text')->shouldReturn($factory2);
    }

    function it_gets_a_registered_factory_with_higher_priority(
        ProductValueFactoryInterface $factory1,
        ProductValueFactoryInterface $factory2
    ) {
        $this->register($factory1);
        $this->register($factory2, 100);

        $factory1->supports('text')->willReturn(true);
        $factory2->supports('text')->willReturn(true);

        $this->get('text')->shouldReturn($factory2);
    }

    function it_throws_an_exception_when_there_is_no_registered_factory(ProductValueFactoryInterface $factory)
    {
        $this->register($factory);
        $factory->supports('text')->willReturn(false);

        $this->shouldThrow('\OutOfBoundsException')->during('get', ['text']);
    }
}
