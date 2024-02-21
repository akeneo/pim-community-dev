<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Updates the updated date of versioned objects
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TimestampableSubscriber implements EventSubscriber
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

        $metadata = $this->em->getClassMetadata($version->getResourceName());
        $haveToBeUpdated = $metadata->getReflectionClass()
            ->implementsInterface(TimestampableInterface::class);

        if (!$haveToBeUpdated) {
            return;
        }

        $related = $this->em->find(
            $version->getResourceName(),
            $version->getResourceName() === Product::class ? $version->getResourceUuid() : $version->getResourceId()
        );

        if (null === $related) {
            return;
        }

        $related->setUpdated($version->getLoggedAt());
        $this->em->getUnitOfWork()->computeChangeSet($metadata, $related);
    }
}
