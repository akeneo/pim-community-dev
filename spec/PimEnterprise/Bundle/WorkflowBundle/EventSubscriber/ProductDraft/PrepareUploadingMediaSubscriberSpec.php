<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\UploadedFileFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

class PrepareUploadingMediaSubscriberSpec extends ObjectBehavior
{
    function let(UploadedFileFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_registers_to_the_before_changes_applying_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::PRE_APPLY => 'prepareMedia'
        ]);
    }

    function it_converts_uploading_media_into_object(
        $factory,
        ProductDraftEvent $event,
        ProductDraft $productDraft
    ) {
        $event->getProductDraft()->willReturn($productDraft);
        $productDraft->getChanges()->willReturn([
            'values' => [
                'foo' => [
                    'media' => [
                        'filePath' => '/tmp/foobar.txt',
                        'originalFilename' => 'foobar',
                        'mimeType' => 'text/plain',
                        'size' => '32',
                    ]
                ]
            ]
        ]);
        $factory->create('/tmp/foobar.txt', 'foobar', 'text/plain', '32')->willReturn('uploading_foo...');

        $changes = [
            'values' => [
                'foo' => [
                    'media' => [
                        'file' => 'uploading_foo...',
                    ]
                ]
            ]
        ];

        $productDraft->setChanges($changes)->shouldBeCalled();

        $this->prepareMedia($event);
    }

    function it_ignores_changes_not_related_to_media(
        ProductDraftEvent $event,
        ProductDraft $productDraft
    ) {
        $changes = [
            'values' => [
                'foo' => [
                    'varchar' => 'bar'
                ]
            ]
        ];
        $event->getProductDraft()->willReturn($productDraft);
        $productDraft->getChanges()->willReturn($changes);
        $productDraft->setChanges($changes)->shouldBeCalled();

        $this->prepareMedia($event);
    }
}
