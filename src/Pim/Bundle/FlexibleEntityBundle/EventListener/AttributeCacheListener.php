<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Common\EventSubscriber;

use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeRepository;

/**
 * This listener is used to listen to insert or delete
 * event from Doctrine to purge attribute list cache
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCacheListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array('onFlush');
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     *
     * @return null
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $cacheDriver = $entityManager->getConfiguration()->getResultCacheImpl();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof AbstractAttribute) {
                AttributeRepository::clearAttributesCache($entity->getEntityType());
                $cacheDriver->delete(AttributeRepository::getAttributesListCacheId($entity->getEntityType()));

                return;
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof AbstractAttribute) {
                AttributeRepository::clearAttributesCache($entity->getEntityType());
                $cacheDriver->delete(AttributeRepository::getAttributesListCacheId($entity->getEntityType()));

                return;
            }
        }
    }
}
