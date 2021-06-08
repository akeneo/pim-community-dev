<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SavePublishedProductCompletenesses;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DeadlockException;
use Psr\Log\LoggerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SqlSavePublishedProductCompletenesses implements SavePublishedProductCompletenesses
{
    private Connection $connection;
    private LoggerInterface $logger;

    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function save(PublishedProductCompletenessCollection $completenesses): void
    {
        $publishedProductId = $completenesses->publishedProductId();

        $deleteAndInsertFunction = function () use ($completenesses, $publishedProductId) {
            $this->connection->executeQuery($this->getDeleteQuery(), ['publishedProductId' => $publishedProductId]);

            foreach ($completenesses as $completeness) {
                $this->connection->executeQuery(
                    $this->getInsertCompletenessQuery(),
                    [
                        'publishedProductId' => $publishedProductId,
                        'ratio' => $completeness->ratio(),
                        'missingCount' => count($completeness->missingAttributeCodes()),
                        'requiredCount' => $completeness->requiredCount(),
                        'localeCode' => $completeness->localeCode(),
                        'channelCode' => $completeness->channelCode(),
                    ]
                );
                $completenessId = $this->connection->lastInsertId();
                $this->connection->executeUpdate(
                    $this->getInsertMissingAttributesQuery(),
                    [
                        'completenessId' => $completenessId,
                        'attributeCodes' => $completeness->missingAttributeCodes(),
                    ],
                    [
                        'attributeCodes' => Connection::PARAM_STR_ARRAY,
                    ]
                );
            }
        };

        try {
            $this->executeWithRetry($deleteAndInsertFunction, $publishedProductId);
        } catch (DeadlockException $e) {
            $this->executeWithLockOnTable($deleteAndInsertFunction, $publishedProductId);
        }
    }

    /**
     * To avoid to get several dead lock exceptions in a row, we sleep between the retry. It lets the database take a breath and finish the other concurrent transactions triggering the deadlock.
     * There is a random sleep as well, to avoid to restart at the same time the other concurrent processes doing a retry as well.
     */
    private function executeWithRetry(callable $function, int $publishedProductId): void
    {
        $retry = 0;
        $isError = true;
        while (true === $isError) {
            try {
                $this->connection->transactional($function);

                $isError = false;
            } catch (DeadlockException $e) {
                $retry += 1;

                if (5 === $retry) {
                    throw $e;
                }

                $this->logger->warning('Deadlock occurred when persisting the publish product completeness', [
                    'published_product_id' => $publishedProductId,
                    'retry' => $retry
                ]);
                usleep(300000 + rand(50000, $retry * 100000));
            }
        }
    }

    /**
     * We don't catch any exception if an error occurs, because it's the last attempt to insert the data by locking the
     * completeness table.
     * Do note that LOCK TABLE locks also the table in READ mode for all the foreign keys (locale, channel and publish product tables).
     * It means that a concurrent transaction can't insert data in the publish product table at the same time (just read). That's why the foreign check constraint is deactivated to avoid these locks.
     */
    private function executeWithLockOnTable(callable $function, int $publishedProductId): void
    {
        $this->logger->warning(
            'Locking the whole published product completeness table to persist the completeness,
             as it fails after trying 5 times to insert data due to deadlocks.',
            [
                'published_product_id' => $publishedProductId,
            ]
        );

        $value = $this->connection->executeQuery('SELECT @@autocommit')->fetch();
        if (!isset($value['@@autocommit']) && ((int) $value['@@autocommit'] !== 1 || (int) $value['@@autocommit'] !== 0)) {
            throw new \LogicException('Error when getting autocommit parameter from Mysql.');
        }

        $formerAutocommitValue = (int) $value['@@autocommit'];
        try {
            $this->connection->executeQuery('SET autocommit=0');
            $this->connection->executeQuery('LOCK TABLES pimee_workflow_published_product_completeness WRITE');
            $function();
            $this->connection->executeQuery('COMMIT');
        } finally {
            $this->connection->executeQuery('UNLOCK TABLES');
            $this->connection->executeQuery(sprintf('SET autocommit=%d', $formerAutocommitValue));
        }
    }

    private function getDeleteQuery(): string
    {
        return <<<SQL
DELETE FROM pimee_workflow_published_product_completeness
WHERE product_id = :publishedProductId
SQL;
    }

    private function getInsertCompletenessQuery(): string
    {
        return <<<SQL
INSERT INTO pimee_workflow_published_product_completeness(locale_id, channel_id, product_id, ratio, missing_count, required_count)
SELECT locale.id, channel.id, :publishedProductId, :ratio, :missingCount, :requiredCount
FROM pim_catalog_locale locale,
     pim_catalog_channel channel
WHERE locale.code = :localeCode
  AND channel.code = :channelCode
SQL;
    }

    private function getInsertMissingAttributesQuery(): string
    {
        return <<<SQL
INSERT INTO pimee_workflow_published_product_completeness_missing_attribute(completeness_id, missing_attribute_id)
SELECT :completenessId, attribute.id
FROM pim_catalog_attribute attribute
WHERE attribute.code IN (:attributeCodes)
SQL;
    }
}
