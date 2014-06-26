<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\TimestampableInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Aims to add timestambable behavior
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TimestampableListener implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate'
        );
    }

    /**
     * Before insert
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof TimestampableInterface) {
            $object->setCreated(new \DateTime('now', new \DateTimeZone('UTC')));
            $object->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }

    /**
     * Before update
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof AbstractProductValue) {
            $product = $object->getEntity();
            if ($product !== null) {
                $this->updateProductFields($args->getObjectManager(), $product, array('updated'));
            }
        }

        if ($object instanceof TimestampableInterface) {
            $object->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }

    /**
     * Update product fields when a value is updated (ORM specific)
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     * @param array            $fields
     */
    protected function updateProductFields(ObjectManager $manager, ProductInterface $product, $fields)
    {
        $uow = $manager->getUnitOfWork();
        // ORM specific, for Document the value is embedded
        if (method_exists($uow, 'scheduleExtraUpdate')) {
            $now     = new \DateTime('now', new \DateTimeZone('UTC'));
            $changes = array();
            foreach ($fields as $field) {
                $changes[$field] = array(null, $now);
            }
            $uow->scheduleExtraUpdate($product, $changes);
        }
    }
}
