<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\EventSubscriber\ComputeProductRawValuesSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ComputeProductRawValuesSubscriberSpec extends ObjectBehavior
{
    function let(NormalizerInterface $serializer)
    {
        $this->beConstructedWith($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeProductRawValuesSubscriber::class);
    }

    function it_subscribes_to_pre_save_event()
    {
        $subscribed = $this->getSubscribedEvents();
        $subscribed->shouldHaveKey(StorageEvents::PRE_SAVE);
        $subscribed->shouldHaveCount(1);
    }

    function it_bypasses_non_product_entities($serializer, GenericEvent $event, \stdClass $randomEntity)
    {
        $event->getSubject()->willReturn($randomEntity);

        $serializer->normalize(Argument::cetera())->shouldNotBeCalled();
    }

    function it_compute_raw_values_of_a_product($serializer, ProductInterface $product, GenericEvent $event)
    {
        $event->getSubject()->willReturn($product);
        $product->getValues()->willReturn(['value1', 'value2']);

        $serializer->normalize(['value1', 'value2'], 'storage')->willReturn(['storage_value1', 'storage_value2']);
        $product->setRawValues(['storage_value1', 'storage_value2'])->shouldBeCalled();

        $this->computeRawValues($event);
    }
}
