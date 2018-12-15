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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\InMemory;

use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\EmptySuggestedDataQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Memory\InMemoryProductSubscriptionRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class EmptySuggestedDataQuery implements EmptySuggestedDataQueryInterface
{
    /** @var InMemoryProductSubscriptionRepository */
    private $subscriptionRepository;

    /**
     * @param InMemoryProductSubscriptionRepository $subscriptionRepository
     */
    public function __construct(InMemoryProductSubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $subscriptions = $this->subscriptionRepository->getSubscriptions();
        foreach ($subscriptions as $subscription) {
            $subscription->setSuggestedData(new SuggestedData([]));
        }
    }
}
