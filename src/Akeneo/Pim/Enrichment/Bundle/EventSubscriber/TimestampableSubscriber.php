<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Aims to add timestambable behavior
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TimestampableSubscriber implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate'
        ];
    }

    /**
     * Before insert
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof TimestampableInterface) {
            return;
        }

        $object->setCreated(new \DateTime('now', new \DateTimeZone('UTC')));
        $object->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
    }

    /**
     * Before update
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof TimestampableInterface) {
            return;
        }

        // Timestamps are managed by the VersioningBundle in this case
        if ($object instanceof VersionableInterface) {
            return;
        }

        $object->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
    }
}
