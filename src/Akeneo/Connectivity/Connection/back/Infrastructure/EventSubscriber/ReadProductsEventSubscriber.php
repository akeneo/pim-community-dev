<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
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
    /** @var ConnectionContextInterface */
    private $connectionContext;

    /** @var UpdateDataDestinationProductEventCountHandler */
    private $updateDataDestinationProductEventCountHandler;

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
        if (!$this->connectionContext->isCollectable()) {
            return;
        }
        if (0 === count($event->productIds())) {
            return;
        }

        $connection = $this->connectionContext->getConnection();
        if (FlowType::DATA_DESTINATION !== (string) $connection->flowType()) {
            return;
        }

        $this->updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                (string) $connection->code(),
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                count($event->productIds())
            )
        );
    }
}
