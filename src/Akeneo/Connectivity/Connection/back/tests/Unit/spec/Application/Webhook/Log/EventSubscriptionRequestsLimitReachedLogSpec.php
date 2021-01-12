<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionRequestsLimitReachedLog;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionRequestsLimitReachedLogSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('create', [
            666,
            new \DateTimeImmutable('2021-01-01T00:00:00+00:00'),
            90
        ]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(EventSubscriptionRequestsLimitReachedLog::class);
    }

    public function it_returns_the_log(): void
    {
        $this->toLog()->shouldReturn(
            [
                'type' => EventSubscriptionRequestsLimitReachedLog::TYPE,
                'message' => EventSubscriptionRequestsLimitReachedLog::MESSAGE,
                'limit' => 666,
                'retry_after' => 90,
                'limit_reset' => '2021-01-01T00:01:30+00:00'
            ]
        );
    }
}
