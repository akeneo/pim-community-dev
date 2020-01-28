<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProposalTracking;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\ProposalTracking\TrackProposal;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Application\FeatureFlag;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProposalTracking;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class TrackProposalSubscriberSpec extends ObjectBehavior
{
    public function let(TrackProposal $trackProposal, FeatureFlag $proposalTrackingFeature, LoggerInterface $logger)
    {
        $this->beConstructedWith($trackProposal, $proposalTrackingFeature, $logger);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_tracks_approved_proposals(
        TrackProposal $trackProposal,
        FeatureFlag $proposalTrackingFeature,
        GenericEvent $event,
        EntityWithValuesDraftInterface $draft
    ) {
        $proposalTrackingFeature->isEnabled()->willReturn(true);
        $event->getSubject()->willReturn($draft);
        $event->getArgument('comment')->willReturn('Agreed');
        $event->getArgument('updatedValues')->willReturn([
            'name' => [
                [
                    'locale' => 'en_US',
                    'channel' => 'mobile',
                    'value' => 'The wonderful Ziggy',
                ],
                [
                    'locale' => 'fr_FR',
                    'channel' => 'mobile',
                    'value' => 'La merveilleuse Ziggy',
                ],

            ],
            'weight' => [[
                'locale' => null,
                'channel' => null,
                'value' => 42,
            ]]
        ]);

        $trackProposal->track(
            $draft,
            ProposalTracking::STATUS_APPROVED,
            ['name', 'weight'],
            'Agreed'
        )->shouldBeCalled();

        $this->trackApprovedProposal($event);
    }

    public function it_tracks_refused_proposals(
        TrackProposal $trackProposal,
        FeatureFlag $proposalTrackingFeature,
        GenericEvent $event,
        EntityWithValuesDraftInterface $draft
    ) {
        $proposalTrackingFeature->isEnabled()->willReturn(true);
        $event->getSubject()->willReturn($draft);
        $event->getArgument('comment')->willReturn('');
        $event->getArgument('updatedValues')->willReturn([
            'name' => [[
                'locale' => 'en_US',
                'channel' => 'mobile',
                'value' => 'Ziggy',
            ]]
        ]);

        $trackProposal->track(
            $draft,
            ProposalTracking::STATUS_REFUSED,
            ['name'],
            ''
        )->shouldBeCalled();

        $this->trackRefusedProposal($event);
    }

    public function it_does_not_track_proposals_if_the_feature_is_not_enabled(
        TrackProposal $trackProposal,
        FeatureFlag $proposalTrackingFeature,
        GenericEvent $event
    ) {
        $proposalTrackingFeature->isEnabled()->willReturn(false);
        $trackProposal->track(Argument::cetera())->shouldNotBeCalled();

        $this->trackApprovedProposal($event);
        $this->trackRefusedProposal($event);
    }

    public function it_does_not_stop_if_an_error_occurs_during_tracking_an_approved_proposal(
        TrackProposal $trackProposal,
        FeatureFlag $proposalTrackingFeature,
        LoggerInterface $logger,
        GenericEvent $event,
        EntityWithValuesDraftInterface $draft
    ) {
        $proposalTrackingFeature->isEnabled()->willReturn(true);
        $event->getSubject()->willReturn($draft);
        $event->getArgument('comment')->willReturn('');
        $event->getArgument('updatedValues')->willReturn([
            'name' => [[
                'locale' => 'en_US',
                'channel' => 'mobile',
                'value' => 'Ziggy',
            ]]
        ]);

        $trackProposal->track(Argument::cetera())->willThrow(new \Exception('Error!'));
        $logger->error('Unable to track approved proposal', ['message' => 'Error!'])->shouldBeCalled();

        $this->trackApprovedProposal($event);
    }

    public function it_does_not_stop_if_an_error_occurs_during_tracking_a_refused_proposal(
        TrackProposal $trackProposal,
        FeatureFlag $proposalTrackingFeature,
        LoggerInterface $logger,
        GenericEvent $event,
        EntityWithValuesDraftInterface $draft
    ) {
        $proposalTrackingFeature->isEnabled()->willReturn(true);
        $event->getSubject()->willReturn($draft);
        $event->getArgument('comment')->willReturn('');
        $event->getArgument('updatedValues')->willReturn([
            'name' => [[
                'locale' => 'en_US',
                'channel' => 'mobile',
                'value' => 'Ziggy',
            ]]
        ]);

        $trackProposal->track(Argument::cetera())->willThrow(new \Exception('Error!'));
        $logger->error('Unable to track refused proposal', ['message' => 'Error!'])->shouldBeCalled();

        $this->trackRefusedProposal($event);
    }
}
