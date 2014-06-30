<?php

namespace Pim\Bundle\VersioningBundle\EventListener\MongoDBODM;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\VersioningBundle\Model\Version;

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
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * Constructor
     *
     * @param VersionManager       $versionManager
     * @param SmartManagerRegistry $registry
     * @param NormalizerInterface  $normalizer
     */
    public function __construct(
        VersionManager $versionManager,
        SmartManagerRegistry $registry,
        NormalizerInterface $normalizer
    ) {
        $this->versionManager = $versionManager;
        $this->registry       = $registry;
        $this->normalizer     = $normalizer;
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
        $changeset = [];
        if (!$this->versionManager->isRealTimeVersioning()) {
            $changeset = $this->normalizer->normalize($versionable, 'csv', ['versioning' => true]);
        }
        $versions = $this->versionManager->buildVersion($versionable, $changeset);

        foreach ($versions as $version) {
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
            if (!isset($this->versionableObjects[$oid]) && !in_array($oid, $this->versionedObjects)) {
                $this->versionableObjects[$oid] = $versionable;
            }
        }
    }

    /**
     * Compute version change set
     *
     * @param Version $version
     */
    protected function computeChangeSet(Version $version)
    {
        $manager = $this->registry->getManagerForClass(get_class($version));

        if ($version->getChangeset()) {
            $manager->persist($version);
            $manager->getUnitOfWork()->computeChangeSet($manager->getClassMetadata(get_class($version)), $version);
        } else {
            $manager->remove($version);
        }
    }
}
