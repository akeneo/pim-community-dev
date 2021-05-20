<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
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
    private ConnectionContextInterface $connectionContext;
    private UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler;

    public function __construct(
        ConnectionContextInterface $connectionContext,
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler
    ) {
        $this->connectionContext = $connectionContext;
        $this->updateDataDestinationProductEventCountHandler = $updateDataDestinationProductEventCountHandler;
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
        $connectionCode = $this->getConnectionCodeByReadProductsEvent($event);

        if (!$this->connectionIsValid($connectionCode, $event->isEventApi())) {
            throw new \LogicException('The connection is not valid.');
        }

        $this->updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                $connectionCode,
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                $event->getCount()
            )
        );
    }

    private function getConnectionCodeByReadProductsEvent(ReadProductsEvent $event): ?string
    {
        return $event->isEventApi() ? $event->getConnectionCode() : $this->connectionContext->getConnectionCode();
    }

    private function connectionIsValid(?string $connectionCode, bool $isEventApi): bool
    {
        if ($connectionCode === null) {
            return false;
        }

        $connection = $isEventApi
            ? $this->connectionContext->getConnectionByCode($connectionCode)
            : $this->connectionContext->getConnection();

        if ($connection === null) {
            return false;
        }
        if (!$connection->auditable()) {
            return false;
        }
        if (!$connection->hasDataDestinationFlowType()) {
            return false;
        }
        if (!$isEventApi && !$this->connectionContext->areCredentialsValidCombination()) {
            return false;
        }

        return true;
    }
}
