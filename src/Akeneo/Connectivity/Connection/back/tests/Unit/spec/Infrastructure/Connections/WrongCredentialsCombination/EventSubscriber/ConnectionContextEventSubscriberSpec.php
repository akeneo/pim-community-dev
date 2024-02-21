<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\EventSubscriber;

use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationEvent;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContextEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        ConnectionContext $connectionContext
    ): void {
        $this->beConstructedWith($connectionContext);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([ApiAuthenticationEvent::class => ['initializeConnectionContext', 1000]]);
    }

    public function it_initializes_connection_context($connectionContext): void
    {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $connectionContext->setClientId('42')->shouldBeCalled();
        $connectionContext->setUsername('magento_0123')->shouldBeCalled();


        $this->initializeConnectionContext($event);
    }
}
