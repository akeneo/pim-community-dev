<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Updates the updatedAt datetime of products in mongodb.
 *
 * We need the updatedAt to be done on pre_save because we need this information for the normalization of the
 * product in normalizedData as we use the normalizedData.updatedAt property to filter on the updatedAt with the PQB.
 *
 * for more information see @jira PIM-6038
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TimestampableSubscriber implements EventSubscriberInterface
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'updateProductTimestamp'
        ];
    }

    /**
     * Updates the product timestamp in mongodb prior to saving it so that
     * the normalizedData version of the product holds up to date informations.
     *
     * @param GenericEvent $event
     */
    public function updateProductTimestamp(GenericEvent $event)
    {
        $object = $event->getSubject();

        if (!$object instanceof ProductInterface) {
            return;
        }

        if (null === $object->getId()) {
            $object->setCreated(new \DateTime('now', new \DateTimeZone('UTC')));
        }

        $object->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
    }
}
