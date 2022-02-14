<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddUuidSubscriber implements EventSubscriberInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'reIndex',
            StorageEvents::POST_SAVE_ALL => 'reIndexAll',
        ];
    }

    public function reIndex(GenericEvent $event): void
    {
        $product = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;
        if (false === $unitary || !$product instanceof ProductInterface) {
            return;
        }
        $this->fillUuids([$product->getIdentifier()]);
    }

    public function reIndexAll(GenericEvent $event): void
    {
        $products = $event->getSubject();
        if (!reset($products) instanceof ProductInterface) {
            return;
        }

        $identifiers = [];
        foreach ($products as $product) {
            $identifiers[] = $product->getIdentifiers();
        }
        $this->fillUuids($identifiers);
    }

    private function fillUuids(array $identifiers): void
    {
        foreach ($identifiers as $identifier) {
            $this->connection->executeQuery(
                'UPDATE pim_catalog_product SET uuid=UUID_TO_BIN(:uuid) WHERE identifier=:identifier',
                [
                    'uuid' => Uuid::uuid4()->toString(),
                    'identifier' => $identifier
                ]
            );
        }
    }
}
