<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;
use Pim\Bundle\CatalogBundle\EventSubscriber\AddParentAProductSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamily\Event\ParentWasAddedToProduct;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddParentAProductSubscriberSpec extends ObjectBehavior
{
    function let(Query\TurnProductIntoVariantProduct $turnProductIntoVariantProduct)
    {
        $this->beConstructedWith($turnProductIntoVariantProduct);
    }

    function it is initializable()
    {
        $this->shouldHaveType(AddParentAProductSubscriber::class);
    }

    function it is a subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it subscribes to event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ParentWasAddedToProduct::EVENT_NAME => 'turnProductIntoVariantProduct'
        ]);
    }

    function it turns a product into a variant product(
        $turnProductIntoVariantProduct,
        ParentWasAddedToProduct $event,
        VariantProductInterface $variantProduct
    ) {
        $event->turnedProduct()->willReturn($variantProduct);
        $turnProductIntoVariantProduct->__invoke($variantProduct)->shouldBeCalled();

        $this->turnProductIntoVariantProduct($event)->shouldReturn(null);
    }
}
