<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber\OnSave;

use Akeneo\Category\Infrastructure\Elasticsearch\CategoryIndexer;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class UpdateIndexSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CategoryIndexer $categoryIndexer
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'updateIndex',
        ];
    }

    public function updateIndex(GenericEvent $event)
    {
        if (!$event->getSubject() instanceof CategoryInterface) {
            return;
        }

        /** @var CategoryInterface $category */
        $category = $event->getSubject();

        $this->categoryIndexer->index($category->getId());
    }
}
