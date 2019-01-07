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

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\SubscriptionEvents;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class FranklinProposalsCreationSubscriber implements EventSubscriberInterface
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(ProductSubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SubscriptionEvents::FRANKLIN_PROPOSALS_CREATED => 'emptySuggestedData',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function emptySuggestedData(GenericEvent $event): void
    {
        $productIds = $event->getSubject();
        if (!is_array($productIds)) {
            throw new \InvalidArgumentException('Event\'s subject must be an array');
        }

        $this->subscriptionRepository->emptySuggestedDataByProducts($productIds);
    }
}
