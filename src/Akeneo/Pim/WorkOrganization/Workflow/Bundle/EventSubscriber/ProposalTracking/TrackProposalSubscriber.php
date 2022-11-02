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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProposalTracking;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\ProposalTracking\TrackProposal;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Application\FeatureFlag;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProposalTracking;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class TrackProposalSubscriber implements EventSubscriberInterface
{
    /** @var TrackProposal */
    private $trackProposal;

    /** @var FeatureFlag */
    private $proposalTrackingFeature;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(TrackProposal $trackProposal, FeatureFlag $proposalTrackingFeature, LoggerInterface $logger)
    {
        $this->trackProposal = $trackProposal;
        $this->proposalTrackingFeature = $proposalTrackingFeature;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityWithValuesDraftEvents::POST_APPROVE => 'trackApprovedProposal',
            EntityWithValuesDraftEvents::POST_PARTIAL_APPROVE => 'trackApprovedProposal',
            EntityWithValuesDraftEvents::POST_REFUSE => 'trackRefusedProposal',
            EntityWithValuesDraftEvents::POST_PARTIAL_REFUSE => 'trackRefusedProposal',
        ];
    }

    public function trackApprovedProposal(GenericEvent $event): void
    {
        if (!$this->proposalTrackingFeature->isEnabled()) {
            return;
        }

        try {
            $this->trackProposal->track(
                $event->getSubject(),
                ProposalTracking::STATUS_APPROVED,
                array_keys($event->getArgument('updatedValues')),
                $event->getArgument('comment') ?? ''
            );
        } catch (\Exception $exception) {
            $this->logger->error('Unable to track approved proposal', [
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function trackRefusedProposal(GenericEvent $event): void
    {
        if (!$this->proposalTrackingFeature->isEnabled()) {
            return;
        }

        try {
            $this->trackProposal->track(
                $event->getSubject(),
                ProposalTracking::STATUS_REFUSED,
                array_keys($event->getArgument('updatedValues')),
                $event->getArgument('comment') ?? ''
            );
        } catch (\Exception $exception) {
            $this->logger->error('Unable to track refused proposal', [
                'message' => $exception->getMessage()
            ]);
        }
    }
}
