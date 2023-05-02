<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\EventSubscriptionFormData;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\CountActiveEventSubscriptionsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAConnectionWebhookQueryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAConnectionWebhookHandler
{
    public function __construct(private GetAConnectionWebhookQueryInterface $getAConnectionWebhookQuery, private int $activeEventSubscriptionsLimit, private CountActiveEventSubscriptionsQueryInterface $countActiveEventSubscriptionsQuery)
    {
    }

    public function handle(GetAConnectionWebhookQuery $query): ?EventSubscriptionFormData
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
