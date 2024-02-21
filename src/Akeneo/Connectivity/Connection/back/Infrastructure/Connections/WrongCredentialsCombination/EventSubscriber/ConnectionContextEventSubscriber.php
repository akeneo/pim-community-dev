<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\EventSubscriber;

use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionContextEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private ConnectionContext $connectionContext)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [ApiAuthenticationEvent::class => ['initializeConnectionContext', 1000]];
    }

    public function initializeConnectionContext(ApiAuthenticationEvent $event): void
    {
        $this->connectionContext->setClientId($event->clientId());
        $this->connectionContext->setUsername($event->username());
    }
}
