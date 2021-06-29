<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Query;

use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery as Query;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\EventSubscriptionFormData;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\CountActiveEventSubscriptionsQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAConnectionWebhookQuery;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAConnectionWebhookHandler
{
    private GetAConnectionWebhookQuery $getAConnectionWebhookQuery;
    private int $activeEventSubscriptionsLimit;
    private CountActiveEventSubscriptionsQuery $countActiveEventSubscriptionsQuery;

    public function __construct(
        GetAConnectionWebhookQuery $getAConnectionWebhookQuery,
        int $activeEventSubscriptionsLimit,
        CountActiveEventSubscriptionsQuery $countActiveEventSubscriptionsQuery
    ) {
        $this->getAConnectionWebhookQuery = $getAConnectionWebhookQuery;
        $this->activeEventSubscriptionsLimit = $activeEventSubscriptionsLimit;
        $this->countActiveEventSubscriptionsQuery = $countActiveEventSubscriptionsQuery;
    }

    public function handle(Query $query): ?EventSubscriptionFormData
    {
        $webhook = $this->getAConnectionWebhookQuery->execute($query->code());
        if (null === $webhook) {
            return null;
        }

        $activeEventSubscriptionsCount = $this->countActiveEventSubscriptionsQuery->execute();

        return new EventSubscriptionFormData(
            $webhook,
            $this->activeEventSubscriptionsLimit,
            $activeEventSubscriptionsCount,
        );
    }
}
