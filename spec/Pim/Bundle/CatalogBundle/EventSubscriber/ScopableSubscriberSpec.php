<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Component\Catalog\Model\ProductInterface;

class ScopableSubscriberSpec extends ObjectBehavior
{
    function let(CatalogContext $context)
    {
        $this->beConstructedWith($context);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_postLoad()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_configures_the_product_scope(
        CatalogContext $context,
        LifecycleEventArgs $args,
        ProductInterface $product
    ) {
        $args->getObject()->willReturn($product);
        $context->hasScopeCode()->willReturn(true);
        $context->getScopeCode()->willReturn('print');
        $product->setScope('print')->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_does_not_configure_scope_for_other_objects(
        CatalogContext $context,
        LifecycleEventArgs $args
    ) {
        $object = new \stdClass();
        $args->getObject()->willReturn($object);
        $context->hasScopeCode()->willReturn(true);
        $context->getScopeCode()->shouldNotBeCalled();
        $this->postLoad($args);
    }
}
