<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendMessageToWebhooksCommand;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SendMessageToWebhooksCommandSpec extends ObjectBehavior
{
    public function let(BusinessEventInterface $businessEvent): void
    {
        $this->beConstructedWith($businessEvent);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SendMessageToWebhooksCommand::class);
    }

    public function it_returns_the_business_event($businessEvent): void
    {
        $this->businessEvent()->shouldReturn($businessEvent);
    }
}
