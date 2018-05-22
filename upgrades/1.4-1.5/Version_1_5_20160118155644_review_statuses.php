<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelperEE;
use Pim\Upgrade\UpgradeHelper;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Migration for ProductDraft changes statuses. In order to manage the partial reject we added in the changes json
 * an index for attributes unitary statuses. This migration aim to add these statuses in current ProductDraft changes.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class Version_1_5_20160118155644_review_statuses extends AbstractMigration implements ContainerAwareInterface
{
    const BATCH_SIZE = 1000;

    /** @var ContainerInterface */
    protected $container;

    /** @var int */
    protected $fromLimit = 0;

    /** @var int */
    protected $toLimit = 0;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $upgradeHelper = new UpgradeHelper($this->container);
        if ($upgradeHelper->areProductsStoredInMongo()) {
            $this->migrateMongoDBDatabase();
        } else {
            $this->migrateMySQLDatabase();
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param int|string $draftStatus '0', 0, '1', or 1
     *
     * @throws AbortMigrationException
     *
     * @return string
     */
    private function getChangeStatusFromDraftStatus($draftStatus)
    {
        $draftStatus = (int) $draftStatus;

        if (EntityWithValuesDraftInterface::IN_PROGRESS === $draftStatus) {
            return EntityWithValuesDraftInterface::CHANGE_DRAFT;
        }

        if (EntityWithValuesDraftInterface::READY === $draftStatus) {
            return EntityWithValuesDraftInterface::CHANGE_TO_REVIEW;
        }

        throw new AbortMigrationException(
            'Invalid data from database : product drafts status must be (int|string) 0 or (int|string) 1.'
        );
    }

    /**
     * Migrates a MySQL database
     */
    private function migrateMySQLDatabase()
    {
        while (null !== $draftsChanges = $this->getNextProductDraftsChanges()) {
            $this->connection->beginTransaction();

            $this->updateProductDraftsChanges($draftsChanges);

            $this->connection->commit();
        }
    }

    /**
     * Migrates a MongoDB database
     */
    private function migrateMongoDBDatabase()
    {
        $schemaHelper    = new SchemaHelperEE($this->container);
        $upgradeHelper   = new UpgradeHelper($this->container);
        $collection      = $schemaHelper->getTableOrCollection('product_draft');
        $draftCollection = new \MongoCollection($upgradeHelper->getMongoInstance(), $collection);

        $drafts = $draftCollection->find();
        foreach ($drafts as $draft) {
            $changeStatus = $this->getChangeStatusFromDraftStatus($draft['status']);
            $statuses     = $this->getReviewStatuses($draft['changes']['values'], $changeStatus);

            $draftCollection->update(
                ['_id' => $draft['_id']],
                [
                    '$set' => ['changes.review_statuses' => $statuses]
                ]
            );
        }
    }

    /**
     * Returns next ProductDraft changes or null if it does not remain.
     *
     * @return array|null
     */
    private function getNextProductDraftsChanges()
    {
        $this->fromLimit = $this->toLimit;
        $this->toLimit   += self::BATCH_SIZE;

        $selectDraftPattern =
            'SELECT draft.id as id, draft.changes as changes, draft.status as status
             FROM pimee_workflow_product_draft as draft LIMIT %s,%s';

        $draftsStmt = $this->connection->query(sprintf($selectDraftPattern, $this->fromLimit, $this->toLimit));
        $draftsStmt->execute();

        return 0 !== $draftsStmt->rowCount() ? $draftsStmt->fetchAll() : null;
    }

    /**
     * Updates ProductDraft changes
     *
     * @param array $draftsChanges
     */
    private function updateProductDraftsChanges(array $draftsChanges)
    {
        $updateSql = 'UPDATE pimee_workflow_product_draft as draft SET draft.changes = :changes WHERE draft.id = :id';

        foreach ($draftsChanges as $draftChanges) {
            $changes      = json_decode($draftChanges['changes'], true);
            $changeStatus = $this->getChangeStatusFromDraftStatus($draftChanges['status']);
            $statuses     = $this->getReviewStatuses($changes['values'], $changeStatus);

            $rawUpdatedChanges = [
                'values'          => $changes['values'],
                'review_statuses' => $statuses
            ];

            $updatedChanges = json_encode($rawUpdatedChanges);

            $updateStmt = $this->connection->prepare($updateSql);
            $updateStmt->bindValue('id', $draftChanges['id']);
            $updateStmt->bindValue('changes', $updatedChanges);
            $updateStmt->execute();
        }
    }

    /**
     * @param array  $values
     * @param string $changeStatus
     *
     * @return array
     */
    private function getReviewStatuses(array $values, $changeStatus)
    {
        $statuses = $values;

        foreach ($statuses as &$items) {
            foreach ($items as &$item) {
                $item['status'] = $changeStatus;
                unset($item['data']);
            }
        }

        return $statuses;
    }
}
