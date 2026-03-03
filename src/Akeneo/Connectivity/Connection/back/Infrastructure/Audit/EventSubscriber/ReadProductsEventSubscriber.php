<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Collect ReadProduct events triggered by the API.
 *
 * Only handle them if
 * - the Connection is collectable
 * - the Connection has a Flow Type Destination
 *
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ReadProductsEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ConnectionContextInterface $connectionContext,
        private UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler,
        private ConnectionRepositoryInterface $connectionRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ReadProductsEvent::class => 'saveReadProducts'];
    }

    /**
     * Save ReadProduct events.
     */
    public function saveReadProducts(ReadProductsEvent $event): void
    {
        if (0 === $event->getCount()) {
            return;
        }

        $connection = $this->getValidConnectionBehindReadProductsEvent($event);

        if (null === $connection) {
            return;
        }

        if (!$connection->auditable()) {
            return;
        }

        if (FlowType::DATA_DESTINATION !== (string)$connection->flowType()) {
            return;
        }

        $this->updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                (string)$connection->code(),
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                $event->getCount()
            )
        );
    }

    /**
     * If the event does not contain a connection code, check if the active connection in the context uses
     * valid credentials, if so, fallback to it.
     */
    private function getValidConnectionBehindReadProductsEvent(ReadProductsEvent $event): ?Connection
    {
        $connectionCode = $event->getConnectionCode();

        if (null !== $connectionCode) {
            return $this->connectionRepository->findOneByCode($connectionCode);
        }

        if (!$this->connectionContext->areCredentialsValidCombination()) {
            return null;
        }

        return $this->connectionContext->getConnection();
    }
}
