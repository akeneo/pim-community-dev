<?php

namespace Pim\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Aims to audit data updates on versionable entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVersionSubscriber implements EventSubscriber
{
    /** @var object[] */
    protected $versionableEntities = [];

    /** @var string[] */
    protected $versionedEntities = [];

    /** @var string[] */
    protected $versions = [];

    /** @var ContainerInterface */
    protected $container;

    /**
     * Constructor. We have to inject the container here as Doctrine event subscribers throws circular
     * reference dependency exceptions if we try to inject services that requiring doctrine.
     * For instance the dependency occurs with the version manager.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Specifies the list of events to listen
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return ['onFlush', 'postFlush'];
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
            $this->container->get('pim_versioning.manager.version')->getObjectManager()->flush();
            $this->detachVersions();
        }
    }

    /**
     * @param object $versionable
     */
    protected function createVersion($versionable)
    {
        $changeset = [];
        if (!$this->container->get('pim_versioning.manager.version')->isRealTimeVersioning()) {
            $changeset = $this->container->get('pim_versioning.serializer')
                ->normalize($versionable, 'flat', ['versioning' => true]);
        }
        $versions = $this->container->get('pim_versioning.manager.version')->buildVersion($versionable, $changeset);

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
        $pendings = $this->container->get('pim_versioning.update_guesser.chained')
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
        $pendings = $this->container->get('pim_versioning.update_guesser.chained')
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
        $pendings = $this->container->get('pim_versioning.update_guesser.chained')
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
        $om = $this->container->get('pim_versioning.manager.version')->getObjectManager();

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
            sha1($this->container->get('pim_versioning.context.version')->getContextInfo())
        );
    }

    /**
     * Clear versions know to this subscribler from the object manager
     */
    protected function detachVersions()
    {
        $om = $this->container->get('pim_versioning.manager.version')->getObjectManager();

        foreach ($this->versions as $version) {
            $om->detach($version);
        }

        $this->versions = [];
        $this->versionableEntities = [];
        $this->versionedEntities = [];
    }
}
