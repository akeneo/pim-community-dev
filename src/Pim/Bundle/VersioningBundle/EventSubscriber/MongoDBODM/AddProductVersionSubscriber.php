<?php

namespace Pim\Bundle\VersioningBundle\EventSubscriber\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Model\Version;
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
    /** @var object[] */
    protected $versionableObjects = [];

    /** @var string[] */
    protected $versionedObjects = [];

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var VersionContext */
    protected $versionContext;

    /**
     * Constructor
     *
     * @param VersionManager      $versionManager
     * @param VersionContext      $versionContext
     * @param NormalizerInterface $normalizer
     */
    public function __construct(
        VersionManager $versionManager,
        VersionContext $versionContext,
        NormalizerInterface $normalizer
    ) {
        $this->versionManager = $versionManager;
        $this->normalizer     = $normalizer;
        $this->versionContext = $versionContext;
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
            $oid = $this->getObjectHash($versionable);
            $currentVersions = $this->createVersion($versionable);
            $versions = array_merge($versions, $currentVersions);
            $this->versionedObjects[] = $oid;
        }

        $this->versionableObjects = [];

        foreach ($versions as $version) {
            $this->applyChangeSet($version);
        }
    }

    /**
     * @param object $versionable
     *
     * @return Version[]
     */
    protected function createVersion($versionable)
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
     * @param object $versionable
     */
    protected function addPendingVersioning($versionable)
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
            $om->getUnitOfWork()->computeChangeSet($om->getClassMetadata(get_class($version)), $version);
            $om->flush($version);
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
        return sprintf('%s#%s', spl_object_hash($object), sha1($this->versionContext->getContextInfo()));
    }
}
