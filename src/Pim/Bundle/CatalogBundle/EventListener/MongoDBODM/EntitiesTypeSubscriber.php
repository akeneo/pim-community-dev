<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Event\PreFlushEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

/**
 * Convert identifiers collection into lazy entity collection
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntitiesTypeSubscriber implements EventSubscriber
{
    /** @var ReferencedCollectionFactory */
    protected $factory;

    /**
     * @param ReferencedCollectionFactory $factory
     */
    public function __construct(ReferencedCollectionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['postLoad', 'prePersist', 'preFlush'];
    }

    /**
     * {@inheritdoc}
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $metadata = $args->getDocumentManager()->getClassMetadata(get_class($document));

        $this->overrideEntitiesField($document, $metadata);
    }

    /**
     * Entities fields need to be overriden on insertion because the postLoad event will not be triggered
     * Otherwise, some documents with entities field (the one that have never been loaded from db in fact)
     * won't have a ReferencedCollection
     *
     * {@inheritdoc}
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $document = $args->getDocument();
        $metadata = $dm->getClassMetadata(get_class($document));

        $this->overrideEntitiesField($document, $metadata);
    }

    public function preFlush(PreFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        foreach ($uow->getScheduledDocumentUpdates() as $document) {
            $metadata = $dm->getClassMetadata(get_class($document));
            $this->synchronizeReferencedCollectionIds($document, $metadata);
        }
    }

    private function overrideEntitiesField($document, ClassMetadata $metadata)
    {
        foreach ($metadata->fieldMappings as $field => $mapping) {
            if ('entities' === $mapping['type']) {
                if (!isset($mapping['targetEntity'])) {
                    throw new \RuntimeException(
                        sprintf(
                            'Please provide the "targetEntity" of the %s::$%s field mapping',
                            $metadata->name,
                            $field
                        )
                    );
                }

                $value = $metadata->reflFields[$mapping['idsField']]->getValue($document);
                if (!$value instanceof ReferencedCollection) {
                    $metadata->reflFields[$field]->setValue(
                        $document,
                        $this->factory->create($mapping['targetEntity'], $value, $document)
                    );
                }
            }
        }
    }

    /**
     * Wait til the very last time to synchronize ids field of the document
     */
    private function synchronizeReferencedCollectionIds($document, ClassMetadata $metadata)
    {
        foreach ($metadata->fieldMappings as $field => $mapping) {
            if ('entities' === $mapping['type']) {
                $oldValue = $metadata->reflFields[$field]->getValue($document);
                if (!$oldValue instanceof ReferencedCollection) {
                    throw new \LogicException(
                        'Property "%s" of "%s" should be an instance of ' .
                        'Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection, got "%s"',
                        $field,
                        get_class($document),
                        is_object($oldValue) ? get_class($oldValue) : gettype($oldValue)
                    );
                }
                $newValue = $oldValue->map(
                    function ($item) {
                        return $item->getId();
                    }
                )
                ->toArray();

                $metadata->reflFields[$mapping['idsField']]->setValue($document, $newValue);
            }
        }
    }
}
