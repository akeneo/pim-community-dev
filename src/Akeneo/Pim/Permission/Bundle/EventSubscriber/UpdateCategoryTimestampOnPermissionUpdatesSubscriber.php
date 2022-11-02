<?php

namespace Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\CategorySaver;
use Akeneo\Pim\Permission\Bundle\Entity\ProductCategoryAccess;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCategoryTimestampOnPermissionUpdatesSubscriber implements EventSubscriberInterface
{
    private CategorySaver $categorySaver;

    public function __construct(CategorySaver $categorySaver)
    {
        $this->categorySaver = $categorySaver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'updateCategoryTimestamp',
            StorageEvents::POST_REMOVE => 'updateCategoryTimestamp',
            StorageEvents::POST_SAVE_ALL => 'updateCategoriesTimestamp',
            StorageEvents::POST_REMOVE_ALL => 'updateCategoriesTimestamp',
        ];
    }

    public function updateCategoryTimestamp(GenericEvent $event)
    {
        if ($event->hasArgument('is_installation') && $event->getArgument('is_installation')) {
            return;
        }

        if ($event->hasArgument('unitary') && false === $event->getArgument('unitary')) {
            return;
        }

        $subject = $event->getSubject();

        if (!$subject instanceof ProductCategoryAccess) {
            return;
        }

        /** @var Category $category */
        $category = $subject->getCategory();

        if (!$category instanceof CategoryInterface) {
            return;
        }

        $category->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));

        $this->categorySaver->save($category);
    }

    public function updateCategoriesTimestamp(GenericEvent $event)
    {
        if ($event->hasArgument('is_installation') && $event->getArgument('is_installation')) {
            return;
        }

        if ($event->hasArgument('unitary') && true === $event->getArgument('unitary')) {
            return;
        }

        $subjects = $event->getSubject();
        $accesses = array_filter($subjects, function ($subject) {
            return $subject instanceof ProductCategoryAccess && $subject->getCategory() instanceof CategoryInterface;
        });
        /** @var CategoryInterface[] $categories */
        $categories = array_map(function (ProductCategoryAccess $access) {
            return $access->getCategory();
        }, $accesses);

        if (empty($categories)) {
            return;
        }

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        foreach ($categories as $category) {
            /** @var Category $category */
            $category->setUpdated($now);
        }

        $this->categorySaver->saveAll($categories);
    }
}
