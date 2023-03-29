<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\EventSubscriber;

use Akeneo\Catalogs\ServiceAPI\Events\AttributeRemovedEvent;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBusInterface;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Connectivity\Connection\Application\Apps\Notifier\AttributeRemovedNotifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotifyOnAttributeRemovalEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly AttributeRemovedNotifierInterface $attributeRemovedNotifier,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [AttributeRemovedEvent::class => 'notifyAttributeIsRemoved'];
    }

    /**
     * @throws \Throwable
     */
    public function notifyAttributeIsRemoved(AttributeRemovedEvent $event)
    {
        $catalogId = $event->getCatalogId();

        $catalog = $this->queryBus->execute(new GetCatalogQuery($catalogId));

        if (null === $catalog) {
            $this->logger->error(
                \sprintf('Notify failed because catalog "%s" does not exist.', $catalogId)
            );
        }

        $this->attributeRemovedNotifier->notify($catalog);
    }
}
