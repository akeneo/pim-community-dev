<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\UpdateWebhookCommand;
use PhpSpec\ObjectBehavior;

class UpdateWebhookCommandSpec extends ObjectBehavior
{
    public function it_is_an_update_webhook_command(): void
    {
        $this->beConstructedWith('magento', false);
        $this->shouldHaveType(UpdateWebhookCommand::class);
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

    public function it_provides_an_url(): void
    {
        $this->beConstructedWith('magento', true, 'http://my-url.com');
        $this->url()->shouldReturn('http://my-url.com');
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
