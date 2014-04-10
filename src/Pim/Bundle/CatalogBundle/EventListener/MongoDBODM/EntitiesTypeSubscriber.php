<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection;
use Doctrine\ODM\MongoDB\Event\PreFlushEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\Common\Collections\Collection;

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
        return ['postLoad', 'prePersist', 'preUpdate'];
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

                $value = $metadata->reflFields[$field]->getValue($document);
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

                $entities = $metadata->reflFields[$field]->getValue($document);
                $metadata->reflFields[$field]->setValue(
                    $document,
                    $entities
                        ->map(
                            function ($item) {
                                if (null === $id = $item->getId()) {
                                    throw new \LogicException(
                                        sprintf(
                                            'Cannot get id of "%s" because it hasn\'t been persisted.',
                                            (string) $item
                                        )
                                    );
                                }

                                return (int) $id;
                            }
                        )
                        ->toArray()
                );
            }
        }
    }

    /**
     * Synchronizes update scheduled documents entities fields before flushing
     * No need to recompute the change set as it hasn't be calculated yet
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
     #   $document = $args->getDocument();
     #   $metadata = $args->getDocumentManager()->getClassMetadata(get_class($document));
     #   foreach ($metadata->fieldMappings as $field => $mapping) {
     #       if ('entities' === $mapping['type'] && $args->hasChangedField($field)) {
     #           $value = $args->getNewValue($field);
     #           if ($value instanceof ReferencedCollection) {
     #               $args->setNewValue(
     #                   $field,
     #                   $value
     #                       ->map(
     #                           function ($item) {
     #                               if (null === $id = $item->getId()) {
     #                                   throw new \LogicException(
     #                                       sprintf(
     #                                           'Cannot get id of "%s" because it hasn\'t been persisted.',
     #                                           (string) $item
     #                                       )
     #                                   );
     #                               }
     #
     #                               return (int) $id;
     #                           }
     #                       )
     #                       ->toArray()
     #               );
     #           }
     #       }
     #   }

        foreach ($args->getDocumentManager()->getUnitOfWork()->documentChangeSets as $i => $changeSets) {
            foreach ($changeSets as $j => $changeSet) {
                if ($changeSet[1] instanceof ReferencedCollection) {
                    $args->getDocumentManager()->getUnitOfWork()->documentChangeSets[$i][$j][1] = 
                        $changeSet[1]
                            ->map(
                                function ($item) {
                                    if (null === $id = $item->getId()) {
                                        throw new \LogicException(
                                            sprintf(
                                                'Cannot get id of "%s" because it hasn\'t been persisted.',
                                                (string) $item
                                            )
                                        );
                                    }

                                    return (int) $id;
                                }
                            )
                            ->toArray();
                }
            }
        }
    }
}
