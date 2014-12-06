<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ChangeSet;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Prophecy\Argument;

class MetadataSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ChangeSetEvents::PREPARE_CHANGE => 'addMetadata',
            ProductDraftEvents::PRE_APPROVE => 'removeMetadata',
        ]);
    }

    function it_adds_value_changeset_metadata(
        ChangeSetEvent $event,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $event->getChangeSet()->willReturn([
            'varchar' => 'foo',
        ]);
        $event->getValue()->willReturn($value);
        $attribute->getCode()->willReturn('name');
        $value->getAttribute()->willReturn($attribute);
        $value->getLocale()->willReturn('en_US');
        $value->getScope()->willReturn('mobile');

        $event
            ->setChangeSet([
                'varchar' => 'foo',
                '__context__' => [
                    'attribute' => 'name',
                    'locale' => 'en_US',
                    'scope' => 'mobile',
                ],
            ])
            ->shouldBeCalled();

        $this->addMetadata($event);
    }

    function it_does_not_add_metadata_when_no_changeset_has_been_computed(
        ChangeSetEvent $event
    ) {
        $event->getChangeSet()->willReturn(null);
        $event->setChangeSet(Argument::any())->shouldNotBeCalled();

        $this->addMetadata($event);
    }

    function it_removes_all_metadata(
        ProductDraftEvent $event,
        ProductDraft $productDraft
    ) {
        $event->getProductDraft()->willReturn($productDraft);
        $productDraft->getChanges()->willReturn([
            'name_en_US' => [
                'varchar' => 'foo',
                '__context__' => [
                    'attribute' => 'name',
                    'locale' => 'en_US',
                    'scope' => 'mobile',
                ],
            ],
            'description' => [
                'text' => 'Lorem Ipsum',
                '__context__' => [
                    'attribute' => 'description',
                    'locale' => null,
                    'scope' => 'mobile',
                ],
            ],
        ]);

        $productDraft
            ->setChanges([
                'name_en_US' => [
                    'varchar' => 'foo',
                ],
                'description' => [
                    'text' => 'Lorem Ipsum',
                ],
            ])
            ->shouldBeCalled();

        $this->removeMetadata($event);
    }

    function it_ignores_missing_metadata_when_removing_them(
        ProductDraftEvent $event,
        ProductDraft $productDraft
    ) {
        $event->getProductDraft()->willReturn($productDraft);
        $productDraft->getChanges()->willReturn([
            'name_en_US' => [
                'varchar' => 'foo',
            ],
            'description' => [
                'text' => 'Lorem Ipsum',
                '__context__' => [
                    'attribute' => 'description',
                    'locale' => null,
                    'scope' => 'mobile',
                ],
            ],
        ]);

        $productDraft
            ->setChanges([
                'name_en_US' => [
                    'varchar' => 'foo',
                ],
                'description' => [
                    'text' => 'Lorem Ipsum',
                ],
            ])
            ->shouldBeCalled();

        $this->removeMetadata($event);
    }
}
