<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use PhpSpec\ObjectBehavior;

class ApiAuthenticationEventSpec extends ObjectBehavior
{
    public function it_is_an_event(): void
    {
        $this->beConstructedWith('magento', '42');
        $this->shouldHaveType(ApiAuthenticationEvent::class);
    }

    public function it_provides_username(): void
    {
        $this->beConstructedWith('magento', '42');
        $this->username()->shouldReturn('magento');
    }

    public function it_provides_client_id()
    {
        $this->beConstructedWith('magento', '42');
        $this->clientId()->shouldReturn('42');
    }
}
