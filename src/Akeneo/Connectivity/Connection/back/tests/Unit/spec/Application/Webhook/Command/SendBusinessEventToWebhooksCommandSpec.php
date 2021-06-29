<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksCommandSpec extends ObjectBehavior
{
    public function let(BulkEventInterface $event): void
    {
        $this->beConstructedWith($event);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SendBusinessEventToWebhooksCommand::class);
    }

    public function it_returns_the_business_event($event): void
    {
        $this->event()->shouldReturn($event);
    }
}
