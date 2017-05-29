<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\NormalizedDataQueryGeneratorInterface;

/**
 * Sets the normalized data of a Product document when related entities are modified
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateNormalizedProductDataSubscriber implements EventSubscriber
{
    /** @var array */
    protected $queryGenerators = [];

    /**
     * Scheduled queries to apply
     *
     * @var string[]
     */
    protected $scheduledQueries = [];

    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $productClass;

    /**
     * @param ManagerRegistry $registry
     * @param string          $productClass
     */
    public function __construct(ManagerRegistry $registry, $productClass)
    {
        $this->registry = $registry;
        $this->productClass = $productClass;
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

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->scheduleQueriesAfterUpdate($entity, $uow->getEntityChangeSet($entity));
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->hasScheduledQueries()) {
            $this->executeQueries();
            $this->purgeScheduledQueries();
        }
    }

    /**
     * Schedule queries related to the entity for normalized data recalculation
     *
     * @param object $entity
     * @param array  $changes
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
     * Get queries for the given entity and updated field
     *
     * @param object $entity
     * @param string $field
     * @param string $oldValue
     * @param string $newValue
     *
     * @return array|null
     */
    protected function generateQuery($entity, $field = '', $oldValue = '', $newValue = '')
    {
        foreach ($this->queryGenerators as $queryGenerator) {
            if ($queryGenerator->supports($entity, $field)) {
                return $queryGenerator->generateQuery($entity, $field, $oldValue, $newValue);
            }
        }

        return null;
    }

    /**
     * Inject query generator
     *
     * @param NormalizedDataQueryGeneratorInterface $queryGenerator
     */
    public function addQueryGenerator(NormalizedDataQueryGeneratorInterface $queryGenerator)
    {
        $this->queryGenerators[] = $queryGenerator;
    }

    /**
     * Execute all scheduled queries
     */
    protected function executeQueries()
    {
        $collection = $this->registry
            ->getManagerForClass($this->productClass)
            ->getDocumentCollection($this->productClass);

        foreach ($this->scheduledQueries as $query) {
            list($query, $compObject, $options) = $query;

            $collection->update($query, $compObject, $options);
        }
    }

    /**
     * Determine if there are scheduled queries
     *
     * @return bool
     */
    protected function hasScheduledQueries()
    {
        return !empty($this->scheduledQueries);
    }

    /**
     * Purge scheduled queries
     */
    protected function purgeScheduledQueries()
    {
        $this->scheduledQueries = [];
    }
}
