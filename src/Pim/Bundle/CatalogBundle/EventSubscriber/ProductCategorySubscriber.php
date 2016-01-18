<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cascade the removal of a category in the products
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @todo      We should configure cascading properly in doctrine to avoid such event subscriber
 */
class ProductCategorySubscriber implements EventSubscriberInterface
{
    /** @var BulkSaverInterface */
    protected $saver;

    /**
     * @param BulkSaverInterface $saver
     */
    public function __construct(BulkSaverInterface $saver)
    {
        $this->saver = $saver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::POST_REMOVE => 'postRemove'];
    }

    /**
     * @param RemoveEvent $event
     */
    public function postRemove(RemoveEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof CategoryInterface) {
            return;
        }

        $productsToUpdate = [];
        foreach ($subject->getProducts() as $product) {
            $product->removeCategory($subject);
            $productsToUpdate[] = $product;
        }

        if (count($productsToUpdate) > 0) {
            $this->saver->saveAll($productsToUpdate, [
                'flush'       => $event->getArgument('flush'),
                'recalculate' => false,
                'schedule'    => false,
            ]);
        }
    }
}
