<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Driver\DriverException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class SqlSavePublishedProductCompletenessesSpec extends ObjectBehavior
{
    function let(Connection $connection, LoggerInterface $logger)
    {
        $this->beConstructedWith($connection, $logger);
    }

    function it_retry_when_deadlock_exception_is_trigger(Connection $connection, DeadlockException $deadlockException)
    {
        $completenesses = new PublishedProductCompletenessCollection(
            42,
            [
                new PublishedProductCompleteness('ecommerce', 'fr_FR', 5, []),
                new PublishedProductCompleteness('ecommerce', 'en_US', 5, ['name']),
            ]
        );
        $callCount = 0;
        $connection->transactional(Argument::type(\Closure::class))->will(function () use (&$callCount) {
            if ($callCount === 2) {
                return;
            }

            $callCount++;
            throw new DeadlockException('', new PDOException(new \PDOException()));
        })->shouldBeCalledTimes(3);

        $this->save($completenesses);
    }

    function it_lock_all_the_completeness_table_after_5_retry(Connection $connection, ResultStatement $autoCommitStatement)
    {
        $completenesses = new PublishedProductCompletenessCollection(
            42,
            [
                new PublishedProductCompleteness('ecommerce', 'fr_FR', 5, []),
                new PublishedProductCompleteness('ecommerce', 'en_US', 5, ['name']),
            ]
        );

        $connection->transactional(Argument::type(\Closure::class))->willThrow(DeadlockException::class)->shouldBeCalledTimes(5);
        $connection->executeQuery('SELECT @@autocommit')->shouldBeCalled()->willReturn($autoCommitStatement);
        $autoCommitStatement->fetch()->willReturn(['@@autocommit' => 1]);
        $connection->executeQuery('SET autocommit=0')->shouldBeCalledTimes(1);
        $connection->executeQuery('LOCK TABLES pimee_workflow_published_product_completeness WRITE')->shouldBeCalledTimes(1);
        $connection->executeQuery('COMMIT')->shouldBeCalledTimes(1);
        $connection->executeQuery(
            "DELETE FROM pimee_workflow_published_product_completeness\nWHERE product_id = :publishedProductId",
            ["publishedProductId" => 42]
        )->shouldBeCalledTimes(1);
        $connection->executeQuery(
            "INSERT INTO pimee_workflow_published_product_completeness(locale_id, channel_id, product_id, ratio, missing_count, required_count)\nSELECT locale.id, channel.id, :publishedProductId, :ratio, :missingCount, :requiredCount\nFROM pim_catalog_locale locale,\n     pim_catalog_channel channel\nWHERE locale.code = :localeCode\n  AND channel.code = :channelCode",
            ["publishedProductId" => 42, "ratio" => 100, "missingCount" => 0, "requiredCount" => 5, "localeCode" => "fr_FR", "channelCode" => "ecommerce"]
        )->shouldBeCalledTimes(1);
        $connection->executeQuery(
            "INSERT INTO pimee_workflow_published_product_completeness(locale_id, channel_id, product_id, ratio, missing_count, required_count)\nSELECT locale.id, channel.id, :publishedProductId, :ratio, :missingCount, :requiredCount\nFROM pim_catalog_locale locale,\n     pim_catalog_channel channel\nWHERE locale.code = :localeCode\n  AND channel.code = :channelCode",
            ["publishedProductId" => 42, "ratio" => 80, "missingCount" => 1, "requiredCount" => 5, "localeCode" => "en_US", "channelCode" => "ecommerce"]
        )->shouldBeCalledTimes(1);
        $connection->lastInsertId()->shouldBeCalled()->willReturn(1000, 1001);
        $connection->executeUpdate(
            "INSERT INTO pimee_workflow_published_product_completeness_missing_attribute(completeness_id, missing_attribute_id)\nSELECT :completenessId, attribute.id\nFROM pim_catalog_attribute attribute\nWHERE attribute.code IN (:attributeCodes)",
            ["completenessId" => 1000, "attributeCodes" => []],
            ["attributeCodes" => Connection::PARAM_STR_ARRAY]
        )->shouldBeCalledTimes(1);
        $connection->executeUpdate(
            "INSERT INTO pimee_workflow_published_product_completeness_missing_attribute(completeness_id, missing_attribute_id)\nSELECT :completenessId, attribute.id\nFROM pim_catalog_attribute attribute\nWHERE attribute.code IN (:attributeCodes)",
            ["completenessId" => 1001, "attributeCodes" => ["name"]],
            ["attributeCodes" => Connection::PARAM_STR_ARRAY]
        )->shouldBeCalledTimes(1);
        $connection->executeQuery('UNLOCK TABLES')->shouldBeCalledTimes(1);
        $connection->executeQuery('SET autocommit=1')->shouldBeCalledTimes(1);

        $this->save($completenesses);
    }
}