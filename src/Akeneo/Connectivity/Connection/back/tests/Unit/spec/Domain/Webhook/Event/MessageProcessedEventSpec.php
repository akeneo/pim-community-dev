<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Event;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MessageProcessedEventSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MessageProcessedEvent::class);
    }
}
