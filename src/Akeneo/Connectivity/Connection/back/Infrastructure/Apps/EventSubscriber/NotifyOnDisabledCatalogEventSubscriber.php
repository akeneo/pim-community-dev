<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\EventSubscriber;

use Akeneo\Catalogs\ServiceAPI\Events\InvalidCatalogDisabledEvent;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBusInterface;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Connectivity\Connection\Application\Apps\Notifier\DisabledCatalogNotifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotifyOnDisabledCatalogEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private DisabledCatalogNotifierInterface $disabledCatalogNotifier,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [InvalidCatalogDisabledEvent::class => 'notifyAllUsersGranted'];
    }

    /**
     * @throws \Throwable
     */
    public function notifyAllUsersGranted(InvalidCatalogDisabledEvent $event)
    {
        $catalogId = $event->getCatalogId();

        $catalog = $this->queryBus->execute(new GetCatalogQuery($catalogId));

        if (null === $catalog) {
            throw new \LogicException(\sprintf('Catalog "%s" does not exist.', $catalogId));
        }

        $this->disabledCatalogNotifier->notify($catalog);
    }
}
