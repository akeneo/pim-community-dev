<?php

namespace Pim\Bundle\VersioningBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\Entity\Pending;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Builder\AuditBuilder;

/**
 * Aims to audit data updates on product, attribute, family, category
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
     * @var VersionableInterface[]
     */
    protected $versionableEntities = array();

    /**
     * @var integer[]
     */
    protected $versionedEntities = array();

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
     * Constructor
     *
     * @param VersionBuilder $versionBuilder
     * @param AuditBuilder   $auditBuilder
     */
    public function __construct(VersionBuilder $versionBuilder, AuditBuilder $auditBuilder)
    {
        $this->versionBuilder = $versionBuilder;
        $this->auditBuilder   = $auditBuilder;
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
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        if (!empty($this->versionableEntities)) {
            if ($this->username) {
                $user = $em->getRepository('OroUserBundle:User')->findOneBy(array('username' => $this->username));
                if (!$user and $this->realTimeVersioning) {
                    $this->versionableEntities = array();

                    return;
                }
                foreach ($this->versionableEntities as $versionable) {
                    if ($this->realTimeVersioning) {
                        $em->refresh($versionable);
                        $this->createVersionAndAudit($em, $versionable, $user);
                    } else {
                        $className = \Doctrine\Common\Util\ClassUtils::getRealClass(get_class($versionable));
                        $pending = new Pending($className, $versionable->getId(), $this->username);
                        $this->computeChangeSet($em, $pending);
                    }
                    $this->versionedEntities[]= spl_object_hash($versionable);
                }
                $this->versionableEntities = array();
                $em->flush();
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Versionable   $versionable
     * @param User          $user
     */
    public function createVersionAndAudit(EntityManager $em, VersionableInterface $versionable, User $user)
    {
        $current = $this->versionBuilder->buildVersion($versionable, $user);
        $this->computeChangeSet($em, $current);
        $previous = $em->getRepository('PimVersioningBundle:Version')->findPreviousVersion($current);
        $previousAudit = $em->getRepository('Oro\Bundle\DataAuditBundle\Entity\Audit')
            ->findOneBy(
                array('objectId' => $current->getResourceId(), 'objectName' => $current->getResourceName()),
                array('loggedAt' => 'desc')
            );
        if ($previousAudit) {
            $versionNumber = $previousAudit->getVersion() + 1;
        } else {
            $versionNumber = 1;
        }

        $audit = $this->auditBuilder->buildAudit($current, $previous, $versionNumber);
        $diffData = $audit->getData();
        if (!empty($diffData)) {
            $this->computeChangeSet($em, $audit);
        }
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
            $this->checkScheduledDeletion($entity);
        }

        foreach ($uow->getScheduledCollectionDeletions() as $entity) {
            $this->checkScheduledCollection($entity);
        }

        foreach ($uow->getScheduledCollectionUpdates() as $entity) {
            $this->checkScheduledCollection($entity);
        }
    }

    /**
     * Check if an entity must be versioned due to entity changes
     *
     * @param EntityManager $em
     * @param object        $entity
     */
    public function checkScheduledUpdate($em, $entity)
    {
        $pendings = $this->versionBuilder->checkScheduledUpdate($em, $entity);
        foreach ($pendings as $pending) {
            $this->addPendingVersioning($pending);
        }
    }

    /**
     * Check if a related entity must be versioned due to entity deletion
     *
     * @param object $entity
     */
    public function checkScheduledDeletion($entity)
    {
        $pendings = $this->versionBuilder->checkScheduledDeletion($entity);
        foreach ($pendings as $pending) {
            $this->addPendingVersioning($pending);
        }
    }

    /**
     * Check if an entity must be versioned due to collection changes
     *
     * @param object $entity
     */
    public function checkScheduledCollection($entity)
    {
        if ($entity->getOwner() instanceof VersionableInterface) {
            $this->addPendingVersioning($entity->getOwner());
        }
    }

    /**
     * Mark entity as to be versioned
     *
     * @param VersionableInterface $versionable
     */
    protected function addPendingVersioning(VersionableInterface $versionable)
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
