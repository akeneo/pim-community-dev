<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM;

use Akeneo\Component\Versioning\BulkVersionBuilderInterface;
use Akeneo\Component\Versioning\Model\Version;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Pim\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Builds versions for a bulk of versionable objects.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BulkVersionBuilder implements BulkVersionBuilderInterface
{
    /** @var VersionBuilder */
    protected $versionBuilder;

    /** @var VersionContext */
    protected $versionContext;

    /** @var DocumentManager */
    protected $documentManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $versionClass;

    /**
     * @param VersionBuilder           $versionBuilder
     * @param VersionContext           $versionContext
     * @param DocumentManager          $documentManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $versionClass
     */
    public function __construct(
        VersionBuilder $versionBuilder,
        VersionContext $versionContext,
        DocumentManager $documentManager,
        EventDispatcherInterface $eventDispatcher,
        $versionClass
    ) {
        $this->versionBuilder  = $versionBuilder;
        $this->versionContext  = $versionContext;
        $this->documentManager = $documentManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->versionClass    = $versionClass;
    }

    /**
     * {@inheritdoc}
     *
     * If a versionable contains no change (i.e. has nothing in its changeset) no version will be built for it.
     */
    public function buildVersions(array $versionables)
    {
        $author = VersionManager::DEFAULT_SYSTEM_USER;
        $event  = $this->eventDispatcher->dispatch(BuildVersionEvents::PRE_BUILD, new BuildVersionEvent());
        if (null !== $event && null !== $event->getUsername()) {
            $author = $event->getUsername();
        }

        $versions = [];
        foreach ($versionables as $versionable) {
            $context         = $this->versionContext->getContextInfo(ClassUtils::getClass($versionable));
            $previousVersion = $this->getPreviousVersion($versionable);
            $newVersion      = $this->versionBuilder->buildVersion($versionable, $author, $previousVersion, $context);

            if (0 < count($newVersion->getChangeSet())) {
                $versions[] = $newVersion;
            }

            if (null !== $previousVersion) {
                $this->documentManager->detach($previousVersion);
            }
        }

        return $versions;
    }

    /**
     * Get the last available version for the provided document
     *
     * @param VersionableInterface $versionable
     *
     * @return Version|null
     */
    protected function getPreviousVersion(VersionableInterface $versionable)
    {
        $resourceName = ClassUtils::getClass($versionable);
        $resourceId   = $versionable->getId();

        $version = $this->documentManager
            ->createQueryBuilder($this->versionClass)
            ->field('resourceName')->equals($resourceName)
            ->field('resourceId')->equals($resourceId)
            ->limit(1)
            ->sort('loggedAt', 'desc')
            ->getQuery()
            ->getSingleResult();

        return $version;
    }
}
