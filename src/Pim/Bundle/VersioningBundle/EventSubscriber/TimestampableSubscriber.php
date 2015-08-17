<?php

namespace Pim\Bundle\VersioningBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\VersioningBundle\Model\Version;

/**
 * Updates the updated date of versioned objects
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TimestampableSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

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

        $relatedManager  = $this->registry->getManagerForClass($version->getResourceName());
        $metadata        = $relatedManager->getClassMetadata($version->getResourceName());
        $haveToBeUpdated = $metadata->getReflectionClass()
            ->implementsInterface('Pim\Bundle\CatalogBundle\Model\TimestampableInterface');

        if (!$haveToBeUpdated) {
            return;
        }

        $related = $relatedManager->find($version->getResourceName(), $version->getResourceId());

        if (null === $related) {
            return;
        }

        $related->setUpdated($version->getLoggedAt());
        $relatedManager->getUnitOfWork()->computeChangeSet($metadata, $related);
    }
}
