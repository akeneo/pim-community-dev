<?php

namespace Akeneo\Catalogs\Infrastructure\EventSubscriber;

use Akeneo\Catalogs\Application\Persistence\GetCatalogsByAttributeOptionQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeOptionRemovalSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GetCatalogsByAttributeOptionQueryInterface $getCatalogsByAttributeOptionQuery,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'disableCatalogsIfAttributeOptionIsRemoved',
        ];
    }

    public function disableCatalogsIfAttributeOptionIsRemoved(GenericEvent $event): void
    {
        $attributeOption = $event->getSubject();
        if (!$attributeOption instanceof AttributeOptionInterface) {
            return;
        }

        $catalogs = $this->getCatalogsByAttributeOptionQuery->execute($attributeOption);

        foreach ($catalogs as $catalog) {
            $this->upsertCatalogQuery->execute(
                $catalog->getId(),
                $catalog->getName(),
                $catalog->getOwnerUsername(),
                false,
            );
        }
    }
}
