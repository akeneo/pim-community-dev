<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ValueObject\Url;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use PhpSpec\ObjectBehavior;

class ConnectionWebhookSpec extends ObjectBehavior
{
    public function it_is_a_connection_webhook_write_model(): void
    {
        $this->beConstructedWith('magento', false);
        $this->shouldHaveType(ConnectionWebhook::class);
    }

    public function it_provides_a_code(): void
    {
        $this->beConstructedWith('magento', false);
        $this->code()->shouldReturn('magento');
    }

    public function it_provides_an_enabled_status(): void
    {
        $this->beConstructedWith('magento', false);
        $this->enabled()->shouldReturn(false);
    }

    public function it_provides_a_url(): void
    {
        $this->beConstructedWith('magento', true, 'http://any-url.com');
        $url = $this->url();
        $url->shouldBeAnInstanceOf(Url::class);
        $url->__toString()->shouldReturn('http://any-url.com');
    }

    public function it_has_no_url_if_an_empty_one_is_provided(): void
    {
        $this->beConstructedWith('magento', true, '');
        $this->url()->shouldReturn(null);
    }

    public function it_could_have_no_url(): void
    {
        $this->beConstructedWith('magento', false);
        $this->url()->shouldReturn(null);
    }

    public function it_provides_the_uuid_use_status(): void
    {
        $this->beConstructedWith('magento', true, 'any-url.com', true);
        $this->isUsingUuid()->shouldReturn(true);
    }

    public function it_could_have_no_uuid_use_status(): void
    {
        $this->beConstructedWith('magento', true, 'any-url.com');
        $this->isUsingUuid()->shouldReturn(false);
    }
}
