<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionFormData
{
    private ConnectionWebhook $eventSubscription;
    private int $activeEventSubscriptionsLimit;
    private int $activeEventSubscriptionsCount;

    public function __construct(
        ConnectionWebhook $eventSubscription,
        int $activeEventSubscriptionsLimit,
        int $activeEventSubscriptionsCount
    ) {
        $this->eventSubscription = $eventSubscription;
        $this->activeEventSubscriptionsLimit = $activeEventSubscriptionsLimit;
        $this->activeEventSubscriptionsCount = $activeEventSubscriptionsCount;
    }

    /**
     * @return array{
     *  event_subscription: array{
     *      connectionCode: string,
     *      enabled: boolean,
     *      secret: ?string,
     *      url: ?string
     *  },
     *  active_event_subscriptions_limit: array{
     *      limit: int,
     *      current: int
     *  }
     * }
     */
    public function normalize(): array
    {
        return [
            'event_subscription' => $this->eventSubscription->normalize(),
            'active_event_subscriptions_limit' => [
                'limit' => $this->activeEventSubscriptionsLimit,
                'current' => $this->activeEventSubscriptionsCount,
            ],
        ];
    }
}
