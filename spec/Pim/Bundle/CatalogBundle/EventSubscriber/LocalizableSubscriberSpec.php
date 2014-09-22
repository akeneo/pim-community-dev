<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

class LocalizableSubscriberSpec extends ObjectBehavior
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

    function it_configures_the_product_locale($context, LifecycleEventArgs $args, AbstractProduct $product)
    {
        $args->getObject()->willReturn($product);
        $context->hasLocaleCode()->willReturn(true);
        $context->getLocaleCode()->willReturn('fr_FR');
        $product->setLocale('fr_FR')->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_configures_the_attribute_option_locale($context, LifecycleEventArgs $args, AttributeOption $option)
    {
        $args->getObject()->willReturn($option);
        $context->hasLocaleCode()->willReturn(true);
        $context->getLocaleCode()->willReturn('fr_FR');
        $option->setLocale('fr_FR')->shouldBeCalled();
        $this->postLoad($args);
    }

    function it_does_not_configure_locale_for_other_objects($context, LifecycleEventArgs $args)
    {
        $object = new \stdClass();
        $args->getObject()->willReturn($object);
        $context->hasLocaleCode()->willReturn(true);
        $context->getLocaleCode()->shouldNotBeCalled();
        $this->postLoad($args);
    }
}
