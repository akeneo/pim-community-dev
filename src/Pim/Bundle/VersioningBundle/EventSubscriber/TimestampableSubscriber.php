<?php

namespace Pim\Bundle\VersioningBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Model\TimestampableInterface;
use Pim\Bundle\VersioningBundle\Model\Version;

/**
 * Class TimestampableSubscriber
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TimestampableSubscriber implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return ['prePersist'];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $version = $args->getObject();

        if (!$version instanceof Version) {
            return;
        }

        $manager = $args->getObjectManager();
        $related = $manager->find($version->getResourceName(), $version->getResourceId());

        if (!$related instanceof TimestampableInterface) {
            return;
        }

        $related->setUpdated($version->getLoggedAt());
    }
}
