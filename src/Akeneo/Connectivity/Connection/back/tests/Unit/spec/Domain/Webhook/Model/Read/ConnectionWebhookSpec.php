<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use PhpSpec\ObjectBehavior;

class ConnectionWebhookSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('magento', true, 'secret_magento', 'any-url.com');
    }

    public function it_is_a_connection_webhook(): void
    {
        $this->beConstructedWith('magento', false);
        $this->shouldHaveType(ConnectionWebhook::class);
    }

    public function it_provides_a_code(): void
    {
        $this->beConstructedWith('magento', false);
        $this->connectionCode()->shouldReturn('magento');
    }

    public function it_could_have_no_secret(): void
    {
        $this->beConstructedWith('magento', false);
        $this->secret()->shouldReturn(null);
    }

    public function it_provides_a_secret(): void
    {
        $this->beConstructedWith('magento', true, 'secret_magento', 'any-url.com');
        $this->secret()->shouldReturn('secret_magento');
    }

    public function it_could_have_no_url(): void
    {
        $this->beConstructedWith('magento', false);
        $this->url()->shouldReturn(null);
    }

    public function it_provides_an_url(): void
    {
        $this->beConstructedWith('magento', true, 'secret_magento', 'any-url.com');
        $this->url()->shouldReturn('any-url.com');
    }

    public function it_provides_the_enabled_status(): void
    {
        $this->beConstructedWith('magento', true);
        $this->enabled()->shouldReturn(true);
    }

    public function it_provides_a_normalized_format(): void
    {
        $this->beConstructedWith('magento', true, 'secret_magento', 'any-url.com');
        $this->normalize()->shouldReturn([
            'connectionCode' => 'magento',
            'enabled' => true,
            'secret' => 'secret_magento',
            'url' => 'any-url.com',
        ]);
    }

    public function it_provides_a_normalized_format_with_no_arguments(): void
    {
        $this->beConstructedWith('magento', false);
        $this->normalize()->shouldReturn([
            'connectionCode' => 'magento',
            'enabled' => false,
            'secret' => null,
            'url' => null,
        ]);
    }
}
