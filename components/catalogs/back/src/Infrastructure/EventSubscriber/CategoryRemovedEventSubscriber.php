<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\EventSubscriber;

use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CategoryRemovedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'disableCatalogsIfCategoryIsRemoved',
        ];
    }

    public function disableCatalogsIfCategoryIsRemoved(GenericEvent $event): void
    {
        $category = $event->getSubject();
        if (!$category instanceof CategoryInterface) {
            return;
        }

//        $attributeCode = $attributeOption->getAttribute()->getCode();
//        $attributeOptionCode = $attributeOption->getCode();
//
//        $catalogs = $this->getEnabledCatalogsByAttributeCodeAndAttributeOptionCodeQuery->execute($attributeCode, $attributeOptionCode);
//
//        foreach ($catalogs as $catalog) {
//            $this->upsertCatalogQuery->execute(
//                $catalog->getId(),
//                $catalog->getName(),
//                $catalog->getOwnerUsername(),
//                false,
//            );
//        }
    }
}
