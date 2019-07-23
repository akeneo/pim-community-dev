<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Aims to audit data updates on versionable entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVersionListener
{
    /** @var object[] */
    protected $versionableEntities = [];

    /** @var string[] */
    protected $versionedEntities = [];

    /** @var string[] */
    protected $versions = [];

    /** @var VersionManager */
    private $versionManager;

    /** @var NormalizerInterface */
    private $versioningNormalizer;

    /** @var UpdateGuesserInterface */
    private $updateGuesser;

    /** @var VersionContext */
    private $versionContext;

    public function __construct(
        VersionManager $versionManager,
        NormalizerInterface $versioningNormalizer,
        UpdateGuesserInterface $updateGuesser,
        VersionContext $versionContext
    ) {
        $this->versionManager = $versionManager;
        $this->versioningNormalizer = $versioningNormalizer;
        $this->updateGuesser = $updateGuesser;
        $this->versionContext = $versionContext;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->checkScheduledUpdate($em, $entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->checkScheduledUpdate($em, $entity);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->checkScheduledDeletion($em, $entity);
        }

        foreach ($uow->getScheduledCollectionDeletions() as $entity) {
            $this->checkScheduledCollection($em, $entity);
        }

        foreach ($uow->getScheduledCollectionUpdates() as $entity) {
            $this->checkScheduledCollection($em, $entity);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->processVersionableEntities();
    }

    /**
     * Process the entities to be versioned
     */
    protected function processVersionableEntities()
    {
        foreach ($this->versionableEntities as $versionable) {
            $oid = $this->getObjectHash($versionable);
            $this->createVersion($versionable);
            $this->versionedEntities[] = $oid;
        }

        $versionedCount = count($this->versionableEntities);
        $this->versionableEntities = [];

        if ($versionedCount) {
            $this->versionManager->getObjectManager()->flush();
            $this->detachVersions();
        }
    }

    /**
     * @param object $versionable
     */
    protected function createVersion($versionable)
    {
        $changeset = [];
        if (!$this->versionManager->isRealTimeVersioning()) {
            $changeset = $this->versioningNormalizer
                ->normalize($versionable, 'flat', ['versioning' => true]);
        }
        $versions = $this->versionManager->buildVersion($versionable, $changeset);

        foreach ($versions as $version) {
            $this->versions[] = $version;
            $this->computeChangeSet($version);
        }
    }

    /**
     * Check if an entity must be versioned due to entity changes
     *
     * @param EntityManager $em
     * @param object        $entity
     */
    protected function checkScheduledUpdate($em, $entity)
    {
        $pendings = $this->updateGuesser
            ->guessUpdates($em, $entity, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);

        foreach ($pendings as $pending) {
            $this->addPendingVersioning($pending);
        }
    }

    /**
     * Check if an entity must be versioned due to collection changes
     *
     * @param EntityManager $em
     * @param object        $entity
     */
    protected function checkScheduledCollection($em, $entity)
    {
        $pendings = $this->updateGuesser
            ->guessUpdates($em, $entity, UpdateGuesserInterface::ACTION_UPDATE_COLLECTION);

        foreach ($pendings as $pending) {
            $this->addPendingVersioning($pending);
        }
    }

    /**
     * Check if a related entity must be versioned due to entity deletion
     *
     * @param EntityManager $em
     * @param object        $entity
     */
    protected function checkScheduledDeletion($em, $entity)
    {
        $pendings = $this->updateGuesser
            ->guessUpdates($em, $entity, UpdateGuesserInterface::ACTION_DELETE);

        foreach ($pendings as $pending) {
            $this->addPendingVersioning($pending);
        }
    }

    /**
     * Mark entity as to be versioned
     *
     * @param object $versionable
     */
    protected function addPendingVersioning($versionable)
    {
        $oid = $this->getObjectHash($versionable);
        if (!isset($this->versionableEntities[$oid]) && !in_array($oid, $this->versionedEntities)) {
            $this->versionableEntities[$oid] = $versionable;
        }
    }

    /**
     * Compute version change set
     *
     * @param Version $version
     */
    protected function computeChangeSet(Version $version)
    {
        $om = $this->versionManager->getObjectManager();

        if ($version->getChangeset()) {
            $om->persist($version);
            $om->getUnitOfWork()->computeChangeSet($om->getClassMetadata(ClassUtils::getClass($version)), $version);
        } else {
            $om->remove($version);
        }
    }

    /**
     * Get an object hash, provides different hashes depending on version manager context to allows to log different
     * versions of a same object during a request
     *
     * @param object $object
     *
     * @return string
     */
    protected function getObjectHash($object)
    {
        return sprintf(
            '%s#%s',
            spl_object_hash($object),
            sha1($this->versionContext->getContextInfo())
        );
    }

    /**
     * Clear versions know to this subscribler from the object manager
     */
    protected function detachVersions()
    {
        $om = $this->versionManager->getObjectManager();

        foreach ($this->versions as $version) {
            $om->detach($version);
        }

        $this->versions = [];
        $this->versionableEntities = [];
        $this->versionedEntities = [];
    }
}
