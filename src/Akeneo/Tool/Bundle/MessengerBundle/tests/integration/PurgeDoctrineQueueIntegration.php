<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MessengerBundle\Query\PurgeDoctrineQueueQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use PHPUnit\Framework\Assert;

class PurgeDoctrineQueueIntegration extends TestCase
{
    private Connection $dbalConnection;
    private PurgeDoctrineQueueQuery $purgeDoctrineQueueQuery;

    public function test_it_purges_the_queue_in_terms_the_queue_and_the_given_datetime(): void
    {
        $this->insertFixtures();

        $this->purgeDoctrineQueueQuery->execute(
            'messenger_messages',
            'webhook',
            new \DateTimeImmutable('2021-01-18 14:16:53')
        );
        $results = $this->fetchRemainingEntries();

        Assert::assertCount(2, $results);
        Assert::assertContains('body', $results);
        Assert::assertContains('product_created', $results);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->purgeDoctrineQueueQuery = $this->get('akeneo_messenger.query.purge');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertFixtures(): void
    {
        $sql = <<<SQL
INSERT INTO messenger_messages (body, headers, queue_name, created_at, available_at)
VALUES 
    (
        'product_model_created',
        '{"class":"Akeneo\\Platform\\Component\\EventQueue\\BulkEvent"}',
        'webhook',
        '2021-01-18 13:16:53',
        '2021-01-18 13:16:53'
    ),
    (
        'product_model_updated',
        '{"class":"Akeneo\\Platform\\Component\\EventQueue\\BulkEvent"}',
        'webhook',
        '2021-01-18 13:20:53',
        '2021-01-18 13:20:53'
    ),
    (
        'body',
        'this is a header',
        'another_queue',
        '2021-01-18 15:16:53',
        '2021-01-18 15:16:53'
    ),
    (
        'product_created',
        '{"class":"Akeneo\\Platform\\Component\\EventQueue\\BulkEvent"}',
        'webhook',
        '2021-01-18 15:32:53',
        '2021-01-18 15:32:53'
    )
SQL;
        $this->dbalConnection->executeQuery($sql);
    }

    private function fetchRemainingEntries(): array
    {
        $sql = <<<SQL
SELECT body
FROM messenger_messages
SQL;

        return $this->dbalConnection->executeQuery($sql)->fetchAll(FetchMode::COLUMN);
    }
}
