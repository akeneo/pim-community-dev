<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeEntityRawValuesSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ComputeEntityRawValuesSubscriberSpec extends ObjectBehavior
{
    function let(NormalizerInterface $serializer)
    {
        $this->beConstructedWith($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeEntityRawValuesSubscriber::class);
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

    function it_computes_raw_values_of_an_entity_with_values(
        $serializer,
        EntityWithValuesInterface $entity,
        GenericEvent $event,
        ValueCollectionInterface $values
    ) {
        $event->getSubject()->willReturn($entity);
        $entity->getValues()->willReturn($values);

        $serializer->normalize($values, 'storage')->willReturn(
            ['storage_value1' => 'data1', 'storage_value2' => 'data2']
        );
        $entity->setRawValues(['storage_value1' => 'data1', 'storage_value2' => 'data2'])->shouldBeCalled();

        $this->computeRawValues($event);
    }

    function it_computes_raw_values_of_a_product_model(
        $serializer,
        ProductModelInterface $rootProductModel,
        ValueCollectionInterface $values,
        ValueInterface $descriptionValue,
        ValueInterface $colorValue,
        ValueInterface $imageValue,
        ValueInterface $value1,
        ValueInterface $value2,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($rootProductModel);
        $rootProductModel->getValuesForVariation()->willReturn($values);
        $values->toArray()->willReturn([$descriptionValue, $colorValue, $imageValue, $value1, $value2]);

        $value1->getAttributeCode()->willReturn('an_attribute');
        $value1->getScopeCode()->willReturn(null);
        $value1->getLocaleCode()->willReturn(null);

        $value2->getAttributeCode()->willReturn('an_attribute');
        $value2->getScopeCode()->willReturn(null);
        $value2->getLocaleCode()->willReturn(null);

        $descriptionValue->getAttributeCode()->willReturn('description');
        $descriptionValue->getScopeCode()->willReturn(null);
        $descriptionValue->getLocaleCode()->willReturn(null);

        $colorValue->getAttributeCode()->willReturn('color');
        $colorValue->getScopeCode()->willReturn(null);
        $colorValue->getLocaleCode()->willReturn(null);

        $imageValue->getAttributeCode()->willReturn('image');
        $imageValue->getScopeCode()->willReturn(null);
        $imageValue->getLocaleCode()->willReturn(null);


        $serializer->normalize(Argument::type(ValueCollectionInterface::class), 'storage')->willReturn(
            ['storage_value1' => 'data1', 'storage_value2' => 'data2']
        );
        $rootProductModel->setRawValues(['storage_value1' => 'data1', 'storage_value2' => 'data2'])->shouldBeCalled();

        $this->computeRawValues($event);
    }

    function it_computes_raw_values_of_a_product(
        $serializer,
        ProductInterface $product,
        ValueCollectionInterface $values,
        ValueInterface $descriptionValue,
        ValueInterface $colorValue,
        ValueInterface $imageValue,
        ValueInterface $value1,
        ValueInterface $value2,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $product->getValuesForVariation()->willReturn($values);
        $values->toArray()->willReturn([$descriptionValue, $colorValue, $imageValue, $value1, $value2]);

        $value1->getAttributeCode()->willReturn('attribute');
        $value1->getScopeCode()->willReturn(null);
        $value1->getLocaleCode()->willReturn(null);

        $value2->getAttributeCode()->willReturn('attribute');
        $value2->getScopeCode()->willReturn(null);
        $value2->getLocaleCode()->willReturn(null);

        $descriptionValue->getAttributeCode()->willReturn('description');
        $descriptionValue->getScopeCode()->willReturn(null);
        $descriptionValue->getLocaleCode()->willReturn(null);

        $colorValue->getAttributeCode()->willReturn('color');
        $colorValue->getScopeCode()->willReturn(null);
        $colorValue->getLocaleCode()->willReturn(null);

        $imageValue->getAttributeCode()->willReturn('image');
        $imageValue->getScopeCode()->willReturn(null);
        $imageValue->getLocaleCode()->willReturn(null);


        $serializer->normalize(Argument::type(ValueCollectionInterface::class), 'storage')->willReturn(
            ['storage_value1' => 'data1', 'storage_value2' => 'data2']
        );
        $product->setRawValues(['storage_value1' => 'data1', 'storage_value2' => 'data2'])->shouldBeCalled();

        $this->computeRawValues($event);
    }
}
