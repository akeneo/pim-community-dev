<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\Pending;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Builder\AuditBuilder;
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
     * Default system user, used for batches
     *
     * @var string
     */
    const DEFAULT_SYSTEM_USER = 'admin';

    /**
     * Entities to version
     *
     * @var object[]
     */
    protected $versionableEntities = [];

    /**
     * @var integer[]
     */
    protected $versionedEntities = [];

    /**
     * @var string
     */
    protected $username = self::DEFAULT_SYSTEM_USER;

    /**
     * @var integer
     */
    protected $realTimeVersioning = true;

    /**
     * @var VersionBuilder
     */
    protected $versionBuilder;

    /**
     * @var AuditBuilder
     */
    protected $auditBuilder;

    /**
     * @var ChainedUpdateGuesser
     */
    protected $guesser;

    /**
     * Constructor
     *
     * @param VersionBuilder       $vBuilder
     * @param AuditBuilder         $aBuilder
     * @param ChainedUpdateGuesser $guesser
     */
    public function __construct(VersionBuilder $vBuilder, AuditBuilder $aBuilder, ChainedUpdateGuesser $guesser)
    {
        $this->versionBuilder = $vBuilder;
        $this->auditBuilder   = $aBuilder;
        $this->guesser        = $guesser;
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
     * @param mixed $username
     *
     * @throws \InvalidArgumentException
     */
    public function setUsername($username)
    {
        if (is_string($username)) {
            $this->username = $username;
        } elseif (is_object($username) && method_exists($username, 'getUsername')) {
            $this->username = (string) $username->getUsername();
        } else {
            throw new \InvalidArgumentException("Username must be a string, or object should have method: getUsername");
        }
    }

    /**
     * @param boolean $mode
     */
    public function setRealTimeVersioning($mode)
    {
        $this->realTimeVersioning = $mode;
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
        if (!empty($this->versionableEntities)) {
            if ($this->username) {
                $user = $em->getRepository('OroUserBundle:User')->findOneBy(['username' => $this->username]);
                if (!$user and $this->realTimeVersioning) {
                    $this->versionableEntities = [];

                    return;
                }
                $this->processVersionableEntities($em, $user);
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param User          $user
     */
    protected function processVersionableEntities(EntityManager $em, User $user)
    {
        foreach ($this->versionableEntities as $versionable) {
            if ($this->realTimeVersioning) {
                $em->refresh($versionable);
                $this->createVersionAndAudit($em, $versionable, $user);
            } else {
                $className = \Doctrine\Common\Util\ClassUtils::getRealClass(get_class($versionable));
                $pending = new Pending($className, $versionable->getId(), $this->username);
                $this->computeChangeSet($em, $pending);
            }
            $this->versionedEntities[] = spl_object_hash($versionable);
        }
        $this->versionableEntities = [];
        $em->flush();
    }

    /**
     * @param EntityManager $em
     * @param object        $versionable
     * @param User          $user
     */
    public function createVersionAndAudit(EntityManager $em, $versionable, User $user)
    {
        $previous = $em->getRepository('PimVersioningBundle:Version')
            ->findPreviousVersion(get_class($versionable), $versionable->getId());
        $prevVersionNumber = ($previous) ? $previous->getVersion() + 1 : 1;

        $current = $this->versionBuilder->buildVersion($versionable, $user, $prevVersionNumber);
        $this->computeChangeSet($em, $current);

        $previousAudit = $em->getRepository('Oro\Bundle\DataAuditBundle\Entity\Audit')
            ->findOneBy(
                ['objectId' => $current->getResourceId(), 'objectName' => $current->getResourceName()],
                ['loggedAt' => 'desc']
            );
        $versionNumber = ($previousAudit) ? $previousAudit->getVersion() + 1 : 1;

        $audit = $this->auditBuilder->buildAudit($current, $previous, $versionNumber);
        $diffData = $audit->getData();
        if (!empty($diffData)) {
            $this->computeChangeSet($em, $audit);
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
