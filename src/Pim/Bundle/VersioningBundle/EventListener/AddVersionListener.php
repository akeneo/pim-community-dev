<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Pim\Bundle\VersioningBundle\Entity\Pending;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ChainedUpdateGuesser;

/**
 * Aims to audit data updates on versionable entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVersionListener implements EventSubscriber
{
    /**
     * Entities to version
     *
     * @var object[]
     */
    protected $versionableEntities = array();

    /**
     * @var integer[]
     */
    protected $versionedEntities = array();

    /**
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * @var ChainedUpdateGuesser
     */
    protected $guesser;

    /**
     * Constructor
     *
     * @param VersionManager       $versionManager
     * @param ChainedUpdateGuesser $guesser
     */
    public function __construct(VersionManager $versionManager, ChainedUpdateGuesser $guesser)
    {
        $this->versionManager = $versionManager;
        $this->guesser        = $guesser;
    }

    /**
     * Specifies the list of events to listen
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array('onFlush', 'postFlush');
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
        $em = $args->getEntityManager();
        $this->processVersionableEntities($em);
    }

    /**
     * @param EntityManager $em
     */
    protected function processVersionableEntities(EntityManager $em)
    {
        foreach ($this->versionableEntities as $versionable) {
            $this->createVersion($em, $versionable);
            $this->versionedEntities[] = spl_object_hash($versionable);
        }

        $versionedCount = count($this->versionableEntities);
        $this->versionableEntities = array();

        if ($versionedCount) {
            $em->flush();
        }
    }

    /**
     * @param EntityManager $em
     * @param object        $versionable
     */
    protected function createVersion(EntityManager $em, $versionable)
    {
        $version = $this->versionManager->buildVersion($versionable);
        if ($version && ($version instanceof Pending || $version->getChangeset())) {
            $this->computeChangeSet($em, $version);
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
        $pendings = $this->guesser->guessUpdates($em, $entity, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
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
        $pendings = $this->guesser->guessUpdates($em, $entity, UpdateGuesserInterface::ACTION_UPDATE_COLLECTION);
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
        $pendings = $this->guesser->guessUpdates($em, $entity, UpdateGuesserInterface::ACTION_DELETE);
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
        $oid = spl_object_hash($versionable);
        if (!isset($this->versionableEntities[$oid]) and !in_array($oid, $this->versionedEntities)) {
            $this->versionableEntities[$oid] = $versionable;
        }
    }

    /**
     * Compute change set
     *
     * @param EntityManager $em
     * @param object        $entity
     */
    protected function computeChangeSet(EntityManager $em, $entity)
    {
        $class = $em->getClassMetadata(get_class($entity));
        $em->persist($entity);
        $em->getUnitOfWork()->computeChangeSet($class, $entity);
    }
}
