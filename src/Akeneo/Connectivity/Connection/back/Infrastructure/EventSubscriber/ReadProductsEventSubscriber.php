<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Collect ReadProduct events triggered by the API.
 *
 * Only handle them if
 * - the autenticated username is the one defined for the Connection
 * - the Connection is auditable
 * - the Connection has a Flow Type Destination
 *
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ReadProductsEventSubscriber implements EventSubscriberInterface
{
    /** @var AreCredentialsValidCombinationQuery */
    private $areCredentialsValidCombinationQuery;

    /** @var SelectConnectionCodeByClientIdQuery */
    private $selectConnectionCodeQuery;

    /** @var ConnectionRepository */
    private $connectionRepository;

    /** @var UpdateDataDestinationProductEventCountHandler */
    private $updateDataDestinationProductEventCountHandler;

    /** @var string */
    private $clientId;

    public function __construct(
        AreCredentialsValidCombinationQuery $areCredentialsValidCombinationQuery,
        SelectConnectionCodeByClientIdQuery $selectConnectionCodeQuery,
        ConnectionRepository $connectionRepository,
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler
    ) {
        $this->areCredentialsValidCombinationQuery = $areCredentialsValidCombinationQuery;
        $this->selectConnectionCodeQuery = $selectConnectionCodeQuery;
        $this->connectionRepository = $connectionRepository;
        $this->updateDataDestinationProductEventCountHandler = $updateDataDestinationProductEventCountHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApiAuthenticationEvent::class => 'checkApiCredentialsCombination',
            ReadProductsEvent::class => 'collectReadProducts',
        ];
    }

    /**
     * Check if the autenticated username is the one defined for the Connection.
     */
    public function checkApiCredentialsCombination(ApiAuthenticationEvent $event): void
    {
        if (false === $this->areCredentialsValidCombinationQuery->execute($event->clientId(), $event->username())) {
            return;
        }

        $this->clientId = $event->clientId();
    }

    /**
     * Collect ReadProduct events.
     */
    public function collectReadProducts(ReadProductsEvent $event): void
    {
        if (null === $this->clientId) {
            return;
        }
        if (0 === count($event->productIds())) {
            return;
        }

        $connection = $this->findConnectionByClientId($this->clientId);
        if (FlowType::DATA_DESTINATION !== (string) $connection->flowType()) {
            return;
        }

        $this->updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                (string) $connection->code(),
                new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
                count($event->productIds())
            )
        );
    }

    /**
     * @throws RuntimeException
     */
    private function findConnectionByClientId(string $clientId): Connection
    {
        $connectionCode = $this->selectConnectionCodeQuery->execute($clientId);

        $connection = $this->connectionRepository->findOneByCode($connectionCode);
        if (null === $connection) {
            throw new RuntimeException(sprintf('Connection with code "%s" not found.', $connectionCode));
        }

        return $connection;
    }
}
