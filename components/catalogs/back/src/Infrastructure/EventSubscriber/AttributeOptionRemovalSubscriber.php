<?php

namespace Akeneo\Catalogs\Infrastructure\EventSubscriber;

use Akeneo\Catalogs\Application\Persistence\GetCatalogsByAttributeOptionQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeOptionRemovalSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GetCatalogsByAttributeOptionQueryInterface $getCatalogsByAttributeOptionQuery,
    ) {
    }
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'disableCatalogsIfAttributeOptionIsRemoved',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function disableCatalogsIfAttributeOptionIsRemoved(GenericEvent $event): void
    {
        $attributeOption = $event->getSubject();
        if (!$attributeOption instanceof AttributeOptionInterface) {
            return;
        }

        $catalogIds = $this->getCatalogsByAttributeOptionQuery->execute($attributeOption);

//        $this->upsertCatalogQuery->execute(
//            $catalogId,
//            $catalog->getName(),
//            $catalog->getOwnerUsername(),
//            $payload['enabled'],
//        );
    }
}
