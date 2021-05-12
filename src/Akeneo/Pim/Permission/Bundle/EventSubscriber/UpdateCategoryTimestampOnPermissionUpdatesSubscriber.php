<?php

namespace Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Entity\ProductCategoryAccess;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCategoryTimestampOnPermissionUpdatesSubscriber implements EventSubscriberInterface
{
    private SaverInterface $categorySaver;

    public function __construct(SaverInterface $categorySaver)
    {
        $this->categorySaver = $categorySaver;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'updateCategoryTimestamp',
            StorageEvents::POST_REMOVE => 'updateCategoryTimestamp',
        ];
    }

    public function updateCategoryTimestamp(GenericEvent $event)
    {
        if ($event->hasArgument('is_installation') && $event->getArgument('is_installation')) {
            return;
        }

        $subject = $event->getSubject();

        if (!$subject instanceof ProductCategoryAccess) {
            return;
        }
        $category = $subject->getCategory();

        if (!$category instanceof CategoryInterface) {
            return;
        }

        $category->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));

        $this->categorySaver->save($category);
    }
}
