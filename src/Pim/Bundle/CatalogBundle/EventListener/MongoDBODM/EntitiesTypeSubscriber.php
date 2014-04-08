<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection;
use Doctrine\ODM\MongoDB\Event\PreFlushEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;

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
     * Replaces entities field value with a reference collection when loading document from DB
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $metadata = $args->getDocumentManager()->getClassMetadata(get_class($document));

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
                if (!isset($mapping['idsField'])) {
                    throw new \RuntimeException(
                        sprintf(
                            'Please provide the "idsField" of the %s::$%s field mapping',
                            $metadata->name,
                            $field
                        )
                    );
                }

                $value = $metadata->reflFields[$field]->getValue($document);

                /**
                 * Entities property should be overrided if it's not a ReferencedCollection or if it's empty
                 */
                if (!$value instanceof ReferencedCollection || $value->count() === 0) {
                    $ids = $metadata->reflFields[$mapping['idsField']]->getValue($document);
                    $metadata->reflFields[$field]->setValue(
                        $document,
                        $this->factory->create($mapping['targetEntity'], $ids, $document)
                    );
                }
            }
        }
    }

    /**
     * Entities fields need to be overriden on insertion because the postLoad event will not be triggered
     * Otherwise, some documents with entities field (the one that have never been loaded from db in fact)
     * won't have a ReferencedCollection
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $metadata = $args->getDocumentManager()->getClassMetadata(get_class($document));

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
                if (!isset($mapping['idsField'])) {
                    throw new \RuntimeException(
                        sprintf(
                            'Please provide the "idsField" of the %s::$%s field mapping',
                            $metadata->name,
                            $field
                        )
                    );
                }

                $entities = $metadata->reflFields[$field]->getValue($document);
                $metadata->reflFields[$field]->setValue(
                    $document,
                    $this->factory->createFromCollection($mapping['targetEntity'], $document, $entities)
                );
            }
        }
    }

    /**
     * Synchronizes update scheduled documents entities fields before flushing
     * No need to recompute the change set as it hasn't be calculated yet
     *
     * @param PreFlushEventArgs $args
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        foreach ($uow->getScheduledDocumentUpdates() as $document) {
            $metadata = $dm->getClassMetadata(get_class($document));
            $this->synchronizeReferencedCollectionIds($document, $metadata);
        }
        foreach ($uow->getScheduledDocumentInsertions() as $document) {
            $metadata = $dm->getClassMetadata(get_class($document));
            $this->synchronizeReferencedCollectionIds($document, $metadata);
        }
    }

    /**
     * Synchronizes ids field with the ids of object contained in the linked "entities" type field
     *
     * @param object        $document
     * @param ClassMetadata $metadata
     *
     * @return null
     */
    private function synchronizeReferencedCollectionIds($document, ClassMetadata $metadata)
    {
        foreach ($metadata->fieldMappings as $field => $mapping) {
            if ('entities' === $mapping['type']) {
                $oldValue = $metadata->reflFields[$field]->getValue($document);
                if (!$oldValue instanceof ReferencedCollection) {
                    throw new \LogicException(
                        sprintf(
                            'Property "%s" of "%s" should be an instance of ' .
                            'Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection, got "%s"',
                            $field,
                            get_class($document),
                            is_object($oldValue) ? get_class($oldValue) : gettype($oldValue)
                        )
                    );
                }
                $newValue = $oldValue->map(
                    function ($item) {
                        if (null === $id = $item->getId()) {
                            throw new \LogicException(
                                sprintf(
                                    'Cannot get id of "%s" because it hasn\'t been persisted.',
                                    (string) $item
                                )
                            );
                        }

                        return $id;
                    }
                )
                ->toArray();

                $metadata->reflFields[$mapping['idsField']]->setValue($document, $newValue);
            }
        }
    }
}
