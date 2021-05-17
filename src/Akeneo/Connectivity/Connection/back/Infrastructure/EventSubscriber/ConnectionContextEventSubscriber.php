<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionContextEventSubscriber implements EventSubscriberInterface
{
    private ConnectionContext $connectionContext;

    public function __construct(ConnectionContext $connectionContext)
    {
        $this->connectionContext = $connectionContext;
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
