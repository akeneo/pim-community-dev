<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\Console\CommandLauncher;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
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
    /** @var CommandLauncher */
    protected $commandLauncher;

    /** @var string */
    protected $logFile;

    /** @var string */
    protected $productClass;

    /** @var array */
    protected $queryGenerators = [];

    /**
     * Scheduled queries to apply
     *
     * @var string[]
     */
    protected $scheduledQueries = [];

    /**
     * @param CommandLauncher $commandLauncher
     * @param string          $productClass
     * @param string          $logFile
     */
    public function __construct(
        CommandLauncher $commandLauncher,
        $productClass,
        $logFile
    ) {
        $this->commandLauncher = $commandLauncher;
        $this->productClass    = $productClass;
        $this->logFile         = $logFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::onFlush, Events::postFlush];
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
        if (empty($this->scheduledQueries)) {
            return;
        }

        $scheduledQueries = json_encode($this->scheduledQueries);

        $command = sprintf(
            'pim:product:update-normalized-data \'%s\'',
            $scheduledQueries
        );

        $this->commandLauncher->executeBackground($command, $this->logFile);
    }
}
