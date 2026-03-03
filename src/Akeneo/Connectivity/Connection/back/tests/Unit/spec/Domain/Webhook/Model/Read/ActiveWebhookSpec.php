<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ActiveWebhookSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('ecommerce', 0, 'a_secret', 'http://localhost/webhook', true);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ActiveWebhook::class);
    }

    public function it_returns_a_connection_code(): void
    {
        $this->connectionCode()
            ->shouldReturn('ecommerce');
    }

    public function it_returns_a_user_id(): void
    {
        $this->userId()
            ->shouldReturn(0);
    }

    public function it_returns_a_secret(): void
    {
        $this->secret()
            ->shouldReturn('a_secret');
    }

    public function it_returns_an_url(): void
    {
        $this->url()
            ->shouldReturn('http://localhost/webhook');
    }

    public function it_returns_uuid_usage_status(): void
    {
        $this->isUsingUuid()
            ->shouldReturn(true);
    }
}
