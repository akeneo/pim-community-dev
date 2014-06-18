<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\WorkflowBundle\EventDispatcher\PropositionEvent;
use PimEnterprise\Bundle\WorkflowBundle\Factory\UploadedFileFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\EventDispatcher\PropositionEvents;

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
            PropositionEvents::PRE_APPLY => 'prepareMedia'
        ]);
    }

    function it_converts_uploading_media_into_object(
        $factory,
        PropositionEvent $event,
        Proposition $proposition
    ) {
        $event->getProposition()->willReturn($proposition);
        $proposition->getChanges()->willReturn([
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

        $proposition->setChanges($changes)->shouldBeCalled();

        $this->prepareMedia($event);
    }

    function it_ignores_changes_not_related_to_media(
        PropositionEvent $event,
        Proposition $proposition
    ) {
        $changes = [
            'values' => [
                'foo' => [
                    'varchar' => 'bar'
                ]
            ]
        ];
        $event->getProposition()->willReturn($proposition);
        $proposition->getChanges()->willReturn($changes);
        $proposition->setChanges($changes)->shouldBeCalled();

        $this->prepareMedia($event);
    }
}
