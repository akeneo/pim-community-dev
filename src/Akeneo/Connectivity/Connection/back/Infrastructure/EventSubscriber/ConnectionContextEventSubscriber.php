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
    /** @var ConnectionContext */
    private $connectionContext;

    /** @var AreCredentialsValidCombinationQuery */
    private $areCredentialsValidCombinationQuery;

    public function __construct(
        ConnectionContext $connectionContext,
        AreCredentialsValidCombinationQuery $areCredentialsValidCombinationQuery
    ) {
        $this->connectionContext = $connectionContext;
        $this->areCredentialsValidCombinationQuery = $areCredentialsValidCombinationQuery;
    }

    public static function getSubscribedEvents(): array
    {
        return [ApiAuthenticationEvent::class => ['initializeConnectionContext', 1000]];
    }

    public function initializeConnectionContext(ApiAuthenticationEvent $event): void
    {
        $this->initializeConnection($event->clientId());
        $this->initializeAreCredentialsValidCombination($event->clientId(), $event->username());
        $this->initializeCollectable();
    }

    private function initializeConnection(): void
    {
        $this->connectionContext->setConnection($connection);
    }

    private function initializeAreCredentialsValidCombination(string $clientId, string $username): void
    {
        $areCredentialsValidCombination = $this->areCredentialsValidCombinationQuery->execute($clientId, $username);

        $this->connectionContext->setAreCredentialsValidCombination($areCredentialsValidCombination);
    }

    private function initializeCollectable(): void
    {
        $this->connectionContext->setCollectable();
    }
}
