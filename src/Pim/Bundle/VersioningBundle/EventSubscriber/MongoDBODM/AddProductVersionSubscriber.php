<?php

namespace Pim\Bundle\VersioningBundle\EventSubscriber\MongoDBODM;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Aims to audit data updates on products stored in MongoDB
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductVersionSubscriber implements EventSubscriber
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
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * Constructor
     *
     * @param VersionManager      $versionManager
     * @param NormalizerInterface $normalizer
     */
    public function __construct(VersionManager $versionManager, NormalizerInterface $normalizer)
    {
        $this->versionManager = $versionManager;
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
        $versions = [];
        foreach ($this->versionableObjects as $versionable) {
            $currentVersions = $this->createVersion($versionable);
            $versions = array_merge($versions, $currentVersions);
            $this->versionedObjects[] = $this->getObjectHash($versionable);
        }

        $this->versionableObjects = array();

        foreach ($versions as $version) {
            $this->applyChangeSet($version);
        }
    }

    /**
     * @param VersionableInterface $versionable
     *
     * @return Version[]
     */
    protected function createVersion(VersionableInterface $versionable)
    {
        $changeset = [];
        if (!$this->versionManager->isRealTimeVersioning()) {
            $changeset = $this->normalizer->normalize($versionable, 'csv', ['versioning' => true]);
        }

        return $this->versionManager->buildVersion($versionable, $changeset);
    }

    /**
     * Mark object as to be versioned
     *
     * @param VersionableInterface $versionable
     */
    protected function addPendingVersioning(VersionableInterface $versionable)
    {
        if ($versionable instanceof ProductInterface) {
            $oid = $this->getObjectHash($versionable);
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
    protected function applyChangeSet(Version $version)
    {
        $om = $this->versionManager->getObjectManager();
        if ($version->getChangeset()) {
            $om->persist($version);
            $om->getUnitOfWork()->computeChangeSet(
                $om->getClassMetadata(ClassUtils::getClass($version)),
                $version
            );
            $om->flush($version);
        } else {
            $om->remove($version);
        }
    }

    /**
     * Get an object hash, provides different hashes depending on version manager context to allows to log different
     * versions of a same object during a request depending on context
     *
     * @param VersionableInterface $versionable
     * @return string
     */
    protected function getObjectHash(VersionableInterface $versionable)
    {
        // TODO add a new class to deal with versionable / versioned entities ?
        return sprintf('%s#%s', spl_object_hash($versionable), sha1($this->versionManager->getContext()));
    }
}
