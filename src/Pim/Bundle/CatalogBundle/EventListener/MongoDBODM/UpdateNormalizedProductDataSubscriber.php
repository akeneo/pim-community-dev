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
    protected $localeClass;

    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $attributeOptionClass;

    /** @var string */
    protected $attributeOptionValueClass;

    /** @var string */
    protected $currencyClass;

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
     * @param string              $localeClass
     * @param string              $attributeClass
     * @param string              $attributeOptionClass
     * @param string              $attributeOptionValueClass
     * @param string              $currencyClass
     */
    public function __construct(
        ManagerRegistry $registry,
        $productClass,
        $familyClass,
        $familyTranslationClass,
        $channelClass,
        $localeClass,
        $attributeClass,
        $attributeOptionClass,
        $attributeOptionValueClass,
        $currencyClass
    ) {
        $this->registry                  = $registry;
        $this->productClass              = $productClass;
        $this->familyClass               = $familyClass;
        $this->familyTranslationClass    = $familyTranslationClass;
        $this->channelClass              = $channelClass;
        $this->localeClass               = $localeClass;
        $this->attributeClass            = $attributeClass;
        $this->attributeOptionClass      = $attributeOptionClass;
        $this->attributeOptionValueClass = $attributeOptionValueClass;
        $this->currencyClass             = $currencyClass;
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
    protected function scheduleQueriesAfterUpdate($entity, $changes)
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
                        $attributeCodes = $this->getPossibleAttributeCodes($attribute, 'normalizedData.');

                        foreach ($attributeCodes as $attributeCode) {
                            $queries[] = [
                                [sprintf('%s', $attributeCode) => [ '$exists' => true ]],
                                ['$unset' => [$attributeCode => '']],
                                ['multi' => true]
                            ];

                        }
                    }

                    return $queries;
                }
            ],
            [
                'class'     => $this->localeClass,
                'field'     => 'activated',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    if (!$newValue) {
                        $attributes = $this->getLocalizableAttributes();
                        $queries = [];

                        foreach ($attributes as $attribute) {
                            $attributeNormFields = [
                                sprintf('normalizedData.%s-%s', $attribute->getCode(), $entity->getCode())
                            ];
                            $channelSuffixes = $this->getChannelSuffixes($attribute);
                            $attributeNormFields = $this->appendSuffixes($attributeNormFields, $channelSuffixes);

                            foreach ($attributeNormFields as $attributeNormField) {
                                $queries[] = [
                                    [sprintf('%s', $attributeNormField) => [ '$exists' => true ]],
                                    ['$unset' => [$attributeNormField => '']],
                                    ['multi' => true]
                                ];
                            }
                        }


                        return $queries;
                    } else {
                        return [];
                    }
                }
            ],
            [
                'class'     => $this->attributeOptionClass,
                'field'     => '',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    $attributeNormFields = $this->getPossibleAttributeCodes(
                        $entity->getAttribute(),
                        'normalizedData.'
                    );

                    $queries = [];

                    foreach ($attributeNormFields as $attributeNormField) {
                        $queries[] = [
                            [$attributeNormField => [ '$exists' => true ]],
                            ['$unset' => [sprintf('%s', $entity->getCode()) => '']],
                            ['multi' => true]
                        ];
                    }

                    return $queries;
                }
            ],
            [
                'class'     => $this->attributeOptionClass,
                'field'     => 'code',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    $attributeNormFields = $this->getPossibleAttributeCodes(
                        $entity->getAttribute(),
                        'normalizedData.'
                    );

                    $queries = [];

                    foreach ($attributeNormFields as $attributeNormField) {
                        $queries[] = [
                            [$attributeNormField => [ '$exists' => true ]],
                            [
                                '$set' => [
                                    sprintf('%s.code', $attributeNormField) => $newValue
                                ]
                            ],
                            ['multi' => true]
                        ];
                    }

                    return $queries;
                }
            ],
            [
                'class'     => $this->attributeOptionValueClass,
                'field'     => 'value',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    $attributeNormFields = $this->getPossibleAttributeCodes(
                        $entity->getOption()->getAttribute(),
                        'normalizedData.'
                    );

                    $queries = [];

                    foreach ($attributeNormFields as $attributeNormField) {
                        $queries[] = [
                            [$attributeNormField => $entity->getOption()->getCode()],
                            [
                                '$set' => [
                                    sprintf(
                                        '%s.code.optionValues.%s.value',
                                        $attributeNormField,
                                        $entity->getLocale()
                                    ) => $newValue
                                ]
                            ],
                            ['multi' => true]
                        ];
                    }

                    return $queries;
                }
            ],
            [
                'class'     => $this->currencyClass,
                'field'     => 'activated',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    if (!$newValue) {
                        $attributeManager = $this->registry->getManagerForClass($this->attributeClass);
                        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

                        $attributes = $attributeRepository->findBy(
                            [
                                'attributeType' => 'pim_catalog_price_collection'
                            ]
                        );

                        $queries = [];

                        foreach ($attributes as $attribute) {
                            $attributeNormFields = $this->getPossibleAttributeCodes($attribute, 'normalizedData.');

                            foreach ($attributeNormFields as $attributeNormField) {
                                $queries[] = [
                                    [sprintf(
                                        '%s',
                                        $attributeNormField,
                                        $entity->getCode()
                                    ) => [ '$exists' => true ]],
                                    ['$unset' => [sprintf(
                                        '%s.%s',
                                        $attributeNormField,
                                        $entity->getCode()
                                    ) => '']],
                                    ['multi' => true]
                                ];
                            }
                        }

                        return $queries;
                    } else {
                        return [];
                    }
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
     * Get scopable attributes
     *
     * @return array
     */
    protected function getLocalizableAttributes()
    {
        $attributeManager = $this->registry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['localizable' => true]);

        return $attributes;
    }

    /**
     * Get possible attribute codes
     *
     * @return array
     */
    protected function getPossibleAttributeCodes(AbstractAttribute $attribute, $prefix = '')
    {
        $localeSuffixes  = $this->getLocaleSuffixes($attribute);
        $channelSuffixes = $this->getChannelSuffixes($attribute);

        $attributeCodes = [($prefix !== '' ? $prefix : '') .$attribute->getCode()];

        $attributeCodes = $this->appendSuffixes($attributeCodes, $localeSuffixes);
        $attributeCodes = $this->appendSuffixes($attributeCodes, $channelSuffixes);

        return $attributeCodes;
    }

    /**
     * Append given suffixes to codes
     * @param  array $codes
     * @param  array $suffixes
     *
     * @return array
     */
    protected function appendSuffixes($codes, $suffixes) {
        $result = $codes;

        if (count($suffixes) > 0) {
            $result = [];

            foreach ($codes as $key => $code) {
                foreach ($suffixes as $suffix) {
                    $result[] = $code . $suffix;
                }
            }
        }

        return $result;
    }

    /**
     * Get all locale prefixes
     *
     * @return array
     */
    protected function getLocaleSuffixes(AbstractAttribute $attribute)
    {
        $localeSuffixes = [];

        if ($attribute->isLocalizable()) {
            foreach ($this->getActivatedLocales() as $locale) {
                $localeSuffixes[] = sprintf('-%s', $locale->getCode());
            }
        }

        return $localeSuffixes;
    }

    /**
     * Get all channel prefixes
     *
     * @return array
     */
    protected function getChannelSuffixes(AbstractAttribute $attribute)
    {
        $channelSuffixes = [];

        if ($attribute->isScopable()) {
            $objectManager     = $this->registry->getManagerForClass($this->channelClass);
            $channelRepository  = $objectManager->getRepository($this->channelClass);

            foreach ($channelRepository->findAll() as $channel) {
                $channelSuffixes[] = sprintf('-%s', $channel->getCode());
            }
        }

        return $channelSuffixes;
    }

    /**
     * Get all activated locale
     *
     * @return array
     */
    protected function getActivatedLocales()
    {
        $objectManager     = $this->registry->getManagerForClass($this->localeClass);
            $localeRepository  = $objectManager->getRepository($this->localeClass);

        return $localeRepository->getActivatedLocales();
    }

    /**
     *
     */
    protected function executeQueries()
    {
    }
}
