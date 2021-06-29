<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\EventSubscriptionFormData;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventSubscriptionFormDataSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(new ConnectionWebhook('erp', true), 3, 2);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EventSubscriptionFormData::class);
    }

    public function it_normalizes(): void
    {
        $this->normalize()->shouldReturn([
            'event_subscription' => [
                'connectionCode' => 'erp',
                'enabled' => true,
                'secret' => null,
                'url' => null,
            ],
            'active_event_subscriptions_limit' => [
                'limit' => 3,
                'current' => 2,
            ],
        ]);
    }
}
