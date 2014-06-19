<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

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

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $familyClass;

    /** @var string */
    protected $familyTranslationClass;

    /**
     * Scheduled queries to apply
     *
     * @var string[]
     */
    protected $scheduledQueries = [];

    /**
     * @param ManagerRegistry     $registry
     * @param NormalizerInterface $normalizer
     * @param string              $productClass
     * @param string              $familyClass
     * @param string              $familyTranslationClass
     */
    public function __construct(
        ManagerRegistry $registry,
        NormalizerInterface $normalizer,
        $productClass,
        $familyClass,
        $familyTranslationClass
    ) {
        $this->registry               = $registry;
        $this->normalizer             = $normalizer;
        $this->productClass           = $productClass;
        $this->familyClass            = $familyClass;
        $this->familyTranslationClass = $familyTranslationClass;
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
            $this->scheduleQueries($entity, $uow->getEntityChangeSet($entity));
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->scheduleQueries($entity, $uow->getEntityChangeSet($entity));
        }

        foreach ($uow->getScheduledCollectionDeletions() as $entity) {
            $this->scheduleQueries($entity, $uow->getEntityChangeSet($entity));
        }

        foreach ($uow->getScheduledCollectionUpdates() as $entity) {
            $this->scheduleQueries($entity, $uow->getEntityChangeSet($entity));
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
    protected function scheduleQueries($entity, array $changes)
    {
        foreach ($changes as $field => $values) {
            list($oldValue, $newValue) = $values;

            $query = $this->generateQuery($entity, $field, $oldValue, $newValue);

            if (null !== $query) {
                $this->scheduledQueries[] = $this->generateQuery($entity, $field, $oldValue, $newValue);
            }
        }
    }

    protected function generateQuery($entity, $field, $oldValue, $newValue)
    {
        $generator = $this->getGenerator($entity, $field);

        if (null !== $generator) {
            return $generator($entity, $field, $oldValue, $newValue);
        } else {
            return null;
        }
    }

    protected function getGenerator($entity, $field) {
        foreach ($this->getQueriesGenerators() as $queriesGenerator) {
            if ($entity instanceof $queriesGenerator['class'] &&
                $field === $queriesGenerator['field']) {
                return $queriesGenerator['generator'];
            }
        }

        return null;
    }

    protected function getQueriesGenerators()
    {
        return [
            [
                'class'     => $this->familyClass,
                'field'     => 'attributeAsLabel',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    return [
                        [
                            'family' => $entity->getId()
                        ],
                        [
                            'normalizedData.family.attributeAsLabel' => (string) $newValue
                        ],
                        [
                            'multi' => true
                        ]
                    ];
                }
            ],
            [
                'class'     => $this->familyTranslationClass,
                'field'     => 'label',
                'generator' => function($entity, $field, $oldValue, $newValue) {
                    return [
                        [
                            'family' => $entity->getId()
                        ],
                        [
                            'normalizedData.family.label.' . $entity->getLocale() => (string) $newValue
                        ],
                        [
                            'multi' => true
                        ]
                    ];
                }
            ]
        ];
    }

    /**
     *
     */
    protected function executeQueries()
    {
        $manager = $this->registry->getManagerForClass($this->productClass);

        foreach ($this->pendingProducts as $productId) {
            $product = $manager->getRepository($this->productClass)->find($productId);
            if ($product) {
                $product->setNormalizedData($this->normalizer->normalize($product, 'mongodb_json'));
                $manager->persist($product);

                $this->updatedProducts[] = $productId;
            }
        }

        $this->pendingProducts = array();

        $updatedCount = count($this->updatedProducts);
        if ($updatedCount) {
            $manager->flush();
        }
    }
}
