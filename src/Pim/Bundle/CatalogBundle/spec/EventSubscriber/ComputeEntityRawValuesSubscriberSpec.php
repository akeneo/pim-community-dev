<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\EventSubscriber\ComputeEntityRawValuesSubscriber;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ComputeEntityRawValuesSubscriberSpec extends ObjectBehavior
{
    function let(NormalizerInterface $serializer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($serializer, $attributeRepository);
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
        AttributeInterface $description,
        AttributeInterface $color,
        AttributeInterface $image,
        AttributeInterface $attribute,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($rootProductModel);
        $rootProductModel->getValuesForVariation()->willReturn($values);
        $values->toArray()->willReturn([$descriptionValue, $colorValue, $imageValue, $value1, $value2]);

        $attribute->getCode()->willReturn('an_attribute');
        $attribute->isUnique()->willReturn(false);
        $value1->getAttribute()->willReturn($attribute);
        $value2->getAttribute()->willReturn($attribute);
        $value1->getScope()->willReturn(null);
        $value1->getLocale()->willReturn(null);
        $value2->getScope()->willReturn(null);
        $value2->getLocale()->willReturn(null);
        $description->getCode()->willReturn('description');
        $description->isUnique()->willReturn(false);
        $descriptionValue->getAttribute()->willReturn($description);
        $descriptionValue->getScope()->willReturn(null);
        $descriptionValue->getLocale()->willReturn(null);
        $color->getCode()->willReturn('color');
        $color->isUnique()->willReturn(false);
        $colorValue->getAttribute()->willReturn($color);
        $colorValue->getScope()->willReturn(null);
        $colorValue->getLocale()->willReturn(null);
        $image->getCode()->willReturn('image');
        $image->isUnique()->willReturn(false);
        $imageValue->getAttribute()->willReturn($image);
        $imageValue->getScope()->willReturn(null);
        $imageValue->getLocale()->willReturn(null);


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
        AttributeInterface $description,
        AttributeInterface $color,
        AttributeInterface $image,
        AttributeInterface $attribute,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $product->getValuesForVariation()->willReturn($values);
        $values->toArray()->willReturn([$descriptionValue, $colorValue, $imageValue, $value1, $value2]);

        $attribute->getCode()->willReturn('an_attribute');
        $attribute->isUnique()->willReturn(false);
        $value1->getAttribute()->willReturn($attribute);
        $value2->getAttribute()->willReturn($attribute);
        $value1->getScope()->willReturn(null);
        $value1->getLocale()->willReturn(null);
        $value2->getScope()->willReturn(null);
        $value2->getLocale()->willReturn(null);
        $description->getCode()->willReturn('description');
        $description->isUnique()->willReturn(false);
        $descriptionValue->getAttribute()->willReturn($description);
        $descriptionValue->getScope()->willReturn(null);
        $descriptionValue->getLocale()->willReturn(null);
        $color->getCode()->willReturn('color');
        $color->isUnique()->willReturn(false);
        $colorValue->getAttribute()->willReturn($color);
        $colorValue->getScope()->willReturn(null);
        $colorValue->getLocale()->willReturn(null);
        $image->getCode()->willReturn('image');
        $image->isUnique()->willReturn(false);
        $imageValue->getAttribute()->willReturn($image);
        $imageValue->getScope()->willReturn(null);
        $imageValue->getLocale()->willReturn(null);


        $serializer->normalize(Argument::type(ValueCollectionInterface::class), 'storage')->willReturn(
            ['storage_value1' => 'data1', 'storage_value2' => 'data2']
        );
        $product->setRawValues(['storage_value1' => 'data1', 'storage_value2' => 'data2'])->shouldBeCalled();

        $this->computeRawValues($event);
    }
}
