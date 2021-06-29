<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckWebhookReachabilityCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('http://172.17.0.1:8000/webhook', '1234');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(CheckWebhookReachabilityCommand::class);
    }

    public function it_returns_the_webhook_url(): void
    {
        $this->webhookUrl()->shouldReturn('http://172.17.0.1:8000/webhook');
    }

    public function it_returns_the_secret(): void
    {
        $this->secret()->shouldReturn('1234');
    }
}

