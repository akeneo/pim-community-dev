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
        $this->beConstructedThrough('fromLimit', [666]);
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
            ]
        );
    }
}
