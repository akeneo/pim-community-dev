<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

class ScopableListenerSpec extends ObjectBehavior
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

    function it_configures_the_product_scope(CatalogContext $context, LifecycleEventArgs $args, AbstractProduct $product)
    {
        $args->getObject()->willReturn($product);
        $context->hasScopeCode()->willReturn(true);
        $context->getScopeCode()->willReturn('print');
        $product->setScope('print')->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_doesnt_configures_scope_for_other_object(CatalogContext $context, LifecycleEventArgs $args)
    {
        $object = new \stdClass();
        $args->getObject()->willReturn($object);
        $context->hasScopeCode()->willReturn(true);
        $context->getScopeCode()->shouldNotBeCalled();
        $this->postLoad($args);
    }

}
