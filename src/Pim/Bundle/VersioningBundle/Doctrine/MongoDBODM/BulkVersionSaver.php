<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\TransformBundle\Normalizer\MongoDB\VersionNormalizer;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Pim\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Service to massively insert versions.
 * Useful for bulk saving of versionable objects.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkVersionSaver
{
    /** @var DocumentManager */
    protected $documentManager;

    /** @var VersionBuilder */
    protected $versionBuilder;

    /** @var VersionManager */
    protected $versionManager;

    /** @var VersionContext */
    protected $versionContext;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $versionClass;

    /**
     * @param DocumentManager          $documentManager
     * @param VersionBuilder           $versionBuilder
     * @param VersionManager           $versionManager
     * @param VersionContext           $versionContext
     * @param NormalizerInterface      $normalizer
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $versionClass
     */
    public function __construct(
        DocumentManager $documentManager,
        VersionBuilder $versionBuilder,
        VersionManager $versionManager,
        VersionContext $versionContext,
        NormalizerInterface $normalizer,
        EventDispatcherInterface $eventDispatcher,
        $versionClass
    ) {
        $this->documentManager = $documentManager;
        $this->versionBuilder  = $versionBuilder;
        $this->versionManager  = $versionManager;
        $this->versionContext  = $versionContext;
        $this->normalizer      = $normalizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->versionClass    = $versionClass;
    }

    /**
     * Bulk generates and inserts full version records for the provided versionable entities in MongoDB.
     * Return an array of ids of documents that have really changed since the last version.
     *
     * @param array $versionables
     *
     * @return array
     */
    public function bulkPersist(array $versionables)
    {
        $versions = [];
        $changedDocIds = [];

        $author = VersionManager::DEFAULT_SYSTEM_USER;
        $event  = $this->eventDispatcher->dispatch(BuildVersionEvents::PRE_BUILD, new BuildVersionEvent());
        if (null !== $event && null !== $event->getUsername()) {
            $author = $event->getUsername();
        }

        foreach ($versionables as $versionable) {
            $context         = $this->versionContext->getContextInfo(ClassUtils::getClass($versionable));
            $previousVersion = $this->getPreviousVersion($versionable);
            $newVersion      = $this->versionBuilder->buildVersion($versionable, $author, $previousVersion, $context);

            if (0 < count($newVersion->getChangeSet())) {
                $versions[]    = $newVersion;
                $changedDocIds = $versionable->getId();
            }

            if (null !== $previousVersion) {
                $this->documentManager->detach($previousVersion);
            }
        }

        $mongodbVersions = [];

        foreach ($versions as $version) {
            $mongodbVersions[] = $this->normalizer->normalize($version, VersionNormalizer::FORMAT);
        }

        if (0 < count($mongodbVersions)) {
            $collection = $this->documentManager->getDocumentCollection($this->versionClass);
            $collection->batchInsert($mongodbVersions);
        }

        return $changedDocIds;
    }

    /**
     * Get the last available version for the provided document
     *
     * @param VersionableInterface $versionable
     *
     * @return Version
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
