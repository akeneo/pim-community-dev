<?php

namespace spec\Oro\Bundle\PimDataGridBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriberConfiguration;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Prophecy\Argument;

class FilterEntityWithValuesSubscriberSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FilterEntityWithValuesSubscriber::class);
    }

    function it_subscribes_to_post_load_event()
    {
        $this->getSubscribedEvents()->shouldReturn([Events::postLoad]);
    }

    function it_does_not_filter_non_entity_with_values_object(\StdClass $entity, LifecycleEventArgs $event)
    {
        $event->getObject()->willReturn($entity);
        $this->postLoad($event);
    }

    function it_does_not_filter_entity_with_values_by_default(
        EntityWithValuesInterface $entity,
        LifecycleEventArgs $event
    ) {
        $entity->setRawValues(Argument::cetera())->shouldNotBeCalled();
        $event->getObject()->willReturn($entity);
        $this->postLoad($event);
    }

    function it_does_not_filter_entity_with_values_when_filtering_not_activated(
        EntityWithValuesInterface $entity,
        LifecycleEventArgs $event
    ) {
        $this->configure(FilterEntityWithValuesSubscriberConfiguration::doNotFilterEntityValues());
        $entity->setRawValues(Argument::cetera())->shouldNotBeCalled();
        $event->getObject()->willReturn($entity);
        $this->postLoad($event);
    }

    function it_filters_raw_values_when_filtering_activated(
        EntityWithValuesInterface $entity,
        LifecycleEventArgs $event
    ) {
        $this->configure(FilterEntityWithValuesSubscriberConfiguration::filterEntityValues(['attribute_1', 'attribute_3']));
        $entity->getRawValues()->willReturn([
            'attribute_1' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_2' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_3' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
        ]);

        $entity->setRawValues([
            'attribute_1' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_3' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
        ])->shouldBeCalled();
        $event->getObject()->willReturn($entity);
        $this->postLoad($event);
    }

    function it_filters_by_keeping_keeps_attribute_as_label_and_image_for_family_entity(
        EntityWithFamilyInterface $entity,
        LifecycleEventArgs $event,
        FamilyInterface $family,
        AttributeInterface $attributeAsImage,
        AttributeInterface $attributeAsLabel
    ) {
        $this->configure(FilterEntityWithValuesSubscriberConfiguration::filterEntityValues(['attribute_1', 'attribute_3']));
        $entity->getRawValues()->willReturn([
            'attribute_1' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_2' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_3' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_as_label' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_as_image' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
        ]);

        $entity->setRawValues([
            'attribute_1' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_3' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_as_label' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'attribute_as_image' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
        ])->shouldBeCalled();

        $entity->getFamily()->willReturn($family);
        $family->getAttributeAsImage()->willReturn($attributeAsImage);
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsImage->getCode()->willReturn('attribute_as_image');
        $attributeAsLabel->getCode()->willReturn('attribute_as_label');

        $event->getObject()->willReturn($entity);
        $this->postLoad($event);

    }
}
