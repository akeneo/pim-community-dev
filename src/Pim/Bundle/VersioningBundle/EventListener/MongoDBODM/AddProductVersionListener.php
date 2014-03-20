<?php

namespace Pim\Bundle\VersioningBundle\EventListener\MongoDBODM;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\VersioningBundle\Entity\Pending;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Builder\AuditBuilder;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;

/**
 * Aims to audit data updates on products stored in MongoDB
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductVersionListener implements EventSubscriber
{
    /**
     * Default system user
     *
     * @var string
     */
    const DEFAULT_SYSTEM_USER = 'admin';

    /**
     * Entities to version
     *
     * @var object[]
     */
    protected $versionableObjects = array();

    /**
     * @var integer[]
     */
    protected $versionedObjects = array();

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
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * @var AuditManager
     */
    protected $auditManager;

    /**
     * @var SmartManagerRegistry
     */
    protected $registry;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * Constructor
     *
     * @param VersionBuilder           $versionBuilder
     * @param AuditBuilder             $auditBuilder
     * @param VersionManager           $versionManager
     * @param AuditManager             $auditManager
     * @param SmartManagerRegistry     $registry
     * @param SecurityContextInterface $securityContext
     * @param UserManager              $userManager
     */
    public function __construct(
        VersionBuilder $versionBuilder,
        AuditBuilder $auditBuilder,
        VersionManager $versionManager,
        AuditManager $auditManager,
        SmartManagerRegistry $registry,
        SecurityContextInterface $securityContext,
        UserManager $userManager
    ) {
        $this->versionBuilder  = $versionBuilder;
        $this->auditBuilder    = $auditBuilder;
        $this->versionManager  = $versionManager;
        $this->auditManager    = $auditManager;
        $this->registry        = $registry;
        $this->securityContext = $securityContext;
        $this->userManager     = $userManager;
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
        $uow = $args->getObjectManager()->getUnitOfWork();

        foreach ($uow->getScheduledDocumentInsertions() as $object) {
            $this->addPendingVersioning($object);
        }

        foreach ($uow->getScheduledDocumentUpdates() as $object) {
            $this->addPendingVersioning($object);
        }

        foreach ($uow->getScheduledDocumentDeletions() as $object) {
            $this->addPendingVersioning($object);
        }

        foreach ($uow->getScheduledCollectionDeletions() as $object) {
            $this->addPendingVersioning($object);
        }

        foreach ($uow->getScheduledCollectionUpdates() as $object) {
            $this->addPendingVersioning($object);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!empty($this->versionableObjects)) {
            $user = $this->getUser();
            if (!$user && $this->realTimeVersioning) {
                $this->versionableObjects = array();
            } else {
                $this->processVersionableEntities($user);
            }
        }
    }

    /**
     * Get the current user
     *
     * @return User|null
     */
    protected function getUser()
    {
        $token = $this->securityContext->getToken();

        if ($token !== null) {
            return $token->getUser();
        }

        return $this->userManager->findUserByUsername(self::DEFAULT_SYSTEM_USER);
    }

    /**
     * @param User $user
     */
    protected function processVersionableEntities(User $user = null)
    {
        foreach ($this->versionableObjects as $versionable) {
            if ($this->realTimeVersioning) {
                $this->createVersionAndAudit($versionable, $user);
            } else {
                $className = \Doctrine\Common\Util\ClassUtils::getRealClass(get_class($versionable));
                $user = $this->getUser();
                $username = $user ? $user->getUserName() : self::DEFAULT_SYSTEM_USER;
                $pending = new Pending($className, $versionable->getId(), $username);
                $this->computeChangeSet($pending);
            }
            $this->versionedObjects[] = spl_object_hash($versionable);
        }

        $versionedCount = count($this->versionableObjects);
        $this->versionableObjects = array();

        if ($versionedCount) {
            foreach ($this->registry->getManagers() as $manager) {
                $manager->flush();
            }
        }
    }

    /**
     * @param object $versionable
     * @param User   $user
     */
    public function createVersionAndAudit($versionable, User $user)
    {
        $previous = $this->versionManager->getVersionRepository()
            ->findPreviousVersion(get_class($versionable), $versionable->getId());
        $prevVersionNumber = ($previous) ? $previous->getVersion() + 1 : 1;

        $current = $this->versionBuilder->buildVersion($versionable, $user, $prevVersionNumber);
        $this->computeChangeSet($current);

        $previousAudit = $this->auditManager->getAuditRepository()
            ->findOneBy(
                array('objectId' => $current->getResourceId(), 'objectName' => $current->getResourceName()),
                array('loggedAt' => 'desc')
            );
        $versionNumber = ($previousAudit) ? $previousAudit->getVersion() + 1 : 1;

        $audit = $this->auditBuilder->buildAudit($current, $previous, $versionNumber);
        $diffData = $audit->getData();
        if (!empty($diffData)) {
            $this->computeChangeSet($audit);
        }
    }

    /**
     * Mark entity as to be versioned
     *
     * @param object $versionable
     */
    protected function addPendingVersioning($versionable)
    {
        if ($versionable instanceof ProductInterface) {
            $oid = spl_object_hash($versionable);
            if (!isset($this->versionableObjects[$oid]) and !in_array($oid, $this->versionedObjects)) {
                $this->versionableObjects[$oid] = $versionable;
            }
        }
    }

    /**
     * Compute change set
     *
     * @param object $object
     */
    protected function computeChangeSet($object)
    {
        $manager = $this->registry->getManagerForClass(get_class($object));
        $class = $manager->getClassMetadata(get_class($object));
        $manager->persist($object);
        $manager->getUnitOfWork()->computeChangeSet($class, $object);
    }
}
