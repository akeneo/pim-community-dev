<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\Subscriber\FranklinProposalsCreationSubscriber;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\SubscriptionEvents;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class FranklinProposalsCreationSubscriberSpec extends ObjectBehavior
{
    public function let(ProductSubscriptionRepositoryInterface $subscriptionRepository): void
    {
        $this->beConstructedWith($subscriptionRepository);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_is_an_franklin_proposals_creation_subscriber(): void
    {
        $this->shouldHaveType(FranklinProposalsCreationSubscriber::class);
    }

    public function it_subscribes_to_proposals_created_event(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(SubscriptionEvents::FRANKLIN_PROPOSALS_CREATED);
    }

    public function it_empties_suggested_data_from_subscriptions($subscriptionRepository): void
    {
        $subscriptionRepository
            ->emptySuggestedDataByProducts([72, 355])
            ->shouldBeCalled();

        $this->emptySuggestedData(new GenericEvent([72, 355]));
    }
}
