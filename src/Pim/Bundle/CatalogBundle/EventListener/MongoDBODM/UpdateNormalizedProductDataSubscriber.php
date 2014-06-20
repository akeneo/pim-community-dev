<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Sets the normalized data of a Product document when related entities are modified
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateNormalizedProductDataSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $familyClass;

    /** @var string */
    protected $familyTranslationClass;

    /** @var string */
    protected $channelClass;

    /** @var string */
    protected $attributeOptionClass;

    /** @var string */
    protected $attributeOptionValueClass;

    /**
     * Scheduled queries to apply
     *
     * @var string[]
     */
    protected $scheduledQueries = [];

    /**
     * @param ManagerRegistry     $registry
     * @param string              $productClass
     * @param string              $familyClass
     * @param string              $familyTranslationClass
     * @param string              $channelClass
     * @param string              $attributeOptionClass
     * @param string              $attributeOptionValueClass
     */
    public function __construct(
        ManagerRegistry $registry,
        EntityManager $entityManager,
        $productClass,
        $familyClass,
        $familyTranslationClass,
        $channelClass,
        $attributeOptionClass,
        $attributeOptionValueClass
    ) {
        $this->registry                  = $registry;
        $this->productClass              = $productClass;
        $this->familyClass               = $familyClass;
        $this->familyTranslationClass    = $familyTranslationClass;
        $this->channelClass              = $channelClass;
        $this->attributeOptionClass      = $attributeOptionClass;
        $this->attributeOptionValueClass = $attributeOptionValueClass;
    }

    /**
     * {@inheritdoc}
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
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->scheduleQueriesAfterUpdate($entity, $uow->getEntityChangeSet($entity));
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->scheduleQueriesAfterDelete($entity);
        }

        foreach ($uow->getScheduledCollectionDeletions() as $entity) {
            $this->scheduleQueriesAfterDelete($entity);
        }

        foreach ($uow->getScheduledCollectionUpdates() as $entity) {
            $this->scheduleQueriesAfterUpdate($entity, $uow->getEntityChangeSet($entity));
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->executeQueries();
    }

    /**
     * Schedule products related to the entity for normalized data recalculation
     *
     * @param object $entity
     */
    protected function scheduleQueriesAfterUpdate($entity, array $changes)
    {
        foreach ($changes as $field => $values) {
            list($oldValue, $newValue) = $values;

            $queries = $this->generateQuery($entity, $field, $oldValue, $newValue);

            if (null !== $queries) {
                $this->scheduledQueries = array_merge(
                    $this->scheduledQueries,
                    $queries
                );
            }
        }
    }

    /**
     * Schedule products related to the entity for normalized data recalculation
     *
     * @param object $entity
     */
    protected function scheduleQueriesAfterDelete($entity)
    {
        $queries = $this->generateQuery($entity);

        if (null !== $queries) {
            $this->scheduledQueries = array_merge(
                $this->scheduledQueries,
                $queries
            );
        }
    }

    /**
     * Get generators for the given entity and updated field
     *
     * @return array|null
     */
    protected function generateQuery($entity, $field = '', $oldValue = '', $newValue = '')
    {
        $generator = $this->getGenerator($entity, $field);

        if (null !== $generator) {
            return $generator($entity, $field, $oldValue, $newValue);
        } else {
            return null;
        }
    }

    /**
     * Get generators for the given entity and updated field
     *
     * @return array|null
     */
    protected function getGenerator($entity, $field) {
        foreach ($this->getQueriesGenerators() as $queriesGenerator) {
            if ($entity instanceof $queriesGenerator['class'] &&
                $field === $queriesGenerator['field']) {
                return $queriesGenerator['generator'];
            }
        }

        return null;
    }

    /**
     * Get queries generators
     *
     * @return array
     */
    protected function getQueriesGenerators()
    {
        return [
            [
                'class'     => $this->familyClass,
                'field'     => 'attributeAsLabel',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    return [[
                        ['family' => $entity->getId()],
                        ['$set' => ['normalizedData.family.attributeAsLabel' => (string) $newValue]],
                        ['multi' => true]
                    ]];
                }
            ],
            [
                'class'     => $this->familyTranslationClass,
                'field'     => 'label',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    return [[
                        ['family' => $entity->getId()],
                        [
                            '$set' => [
                                sprintf('normalizedData.family.label.%s', $entity->getLocale()) => (string) $newValue
                            ]
                        ],
                        ['multi' => true]
                    ]];
                }
            ],
            [
                'class'     => $this->channelClass,
                'field'     => '',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    $attributes = $this->getScopableAttributes();

                    $queries = [];

                    foreach ($attributes as $attribute) {
                        $attributesToRemove = [];

                        if ($attribute->isLocalizable()) {
                            foreach ($entity->getLocales() as $locale) {
                                 $attributesToRemove[] = sprintf(
                                    'normalizedData.%s-%s-%s',
                                    $attribute->getCode(),
                                    $locale->getCode(),
                                    $entity->getCode()
                                );
                            }
                        } else {
                            $attributesToRemove[] = sprintf(
                                'normalizedData.%s-%s',
                                $attribute->getCode(),
                                $entity->getCode()
                            );
                        }

                        $queries[] = [
                            [sprintf('normalizedData.%s', $attribute->getCode()) => [ '$exists' => true ]],
                            ['$unset' => $this->getAttributesToRemove()],
                            ['multi' => true]
                        ];
                    }

                    return [];
                }
            ],
            [
                'class'     => $this->attributeOptionClass,
                'field'     => '',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    return [[
                        [sprintf('normalizedData.%s', $entity->getAttribute()->getCode()) => [ '$exists' => true ]],
                        ['$unset' => [sprintf('normalizedData.%s', $entity->getCode())]],
                        ['multi' => true]
                    ]];
                }
            ],
            [
                'class'     => $this->attributeOptionClass,
                'field'     => 'code',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    return [[
                        [sprintf('normalizedData.%s', $entity->getAttribute()->getCode()) => [ '$exists' => true ]],
                        [
                            '$set' => [
                                sprintf('normalizedData.%s.code', $entity->getAttribute()->getCode()) => $newValue
                            ]
                        ],
                        ['multi' => true]
                    ]];
                }
            ],
            [
                'class'     => $this->attributeOptionValueClass,
                'field'     => 'code',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    return [[
                        [
                            sprintf(
                                'normalizedData.%s',
                                $entity->getOption()->getAttribute()->getCode()
                            ) => [ '$exists' => true ]
                        ],
                        [
                            '$set' => [
                                sprintf(
                                    'normalizedData.%s.code.optionValues%s.value',
                                    $entity->getOption()->getAttribute()->getCode(),
                                    $entity->getLocale()
                                ) => $newValue
                            ]
                        ],
                        ['multi' => true]
                    ]];
                }
            ]
        ];
    }

    /**
     * Get scopable attributes
     *
     * @return array
     */
    protected function getScopableAttributes()
    {
        $attributeManager = $this->registry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['scopable' => true]);

        return $attributes;
    }

    /**
     *
     */
    protected function executeQueries()
    {
        error_log(print_r($this->scheduledQueries, true));
    }
}
