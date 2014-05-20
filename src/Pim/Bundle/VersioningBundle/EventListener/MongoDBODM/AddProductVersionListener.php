<?php

namespace Pim\Bundle\VersioningBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Entity\Pending;
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
     * Objects to version
     *
     * @var object[]
     */
    protected $versionableObjects = array();

    /**
     * @var integer[]
     */
    protected $versionedObjects = array();

    /**
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * @var SmartManagerRegistry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param VersionManager       $versionManager
     * @param SmartManagerRegistry $registry
     */
    public function __construct(VersionManager $versionManager, SmartManagerRegistry $registry)
    {
        $this->versionManager = $versionManager;
        $this->registry       = $registry;
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
        $uow = $args->getObjectManager()->getUnitOfWork();

        foreach ($uow->getScheduledDocumentInsertions() as $object) {
            $this->addPendingVersioning($object);
        }

        foreach ($uow->getScheduledDocumentUpdates() as $object) {
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
        $this->processVersionableObjects();
    }

    /**
     * Process the versionable objects
     */
    protected function processVersionableObjects()
    {
        foreach ($this->versionableObjects as $versionable) {
            $this->createVersion($versionable);
            $this->versionedObjects[] = spl_object_hash($versionable);
        }

        $versionedCount = count($this->versionableObjects);
        $this->versionableObjects = array();

        if ($versionedCount) {
            foreach ($this->registry->getManagers() as $manager) {
                if ($manager instanceof EntityManager) {
                    $manager->flush();
                }
            }
        }
    }

    /**
     * @param object $versionable
     */
    public function createVersion($versionable)
    {
        $version = $this->versionManager->buildVersion($versionable);
        if ($version && ($version instanceof Pending || $version->getChangeset())) {
            $this->computeChangeSet($version);
        }
    }

    /**
     * Mark object as to be versioned
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
