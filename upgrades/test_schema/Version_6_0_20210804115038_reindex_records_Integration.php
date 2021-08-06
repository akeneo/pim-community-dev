<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Elasticsearch\Client;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20210804115038_reindex_records_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210804115038_reindex_records';
    private Connection $connection;

    public function test_does_nothing_when_launched_in_sass_version(): void
    {
        if (!$this->isSassVersion()) {
            $this->markTestSkipped('As version provider cannot be mocked, this test can only be launched on sass version');
        }

        $recordAliasName = $this->getParameter('record_index_name');

        $client = $this->getClient();
        $this->loadReferenceEntityAndRecords();

        $recordsInIndexBeforeMigration = $this->getRecordsCountInIndex($client, $recordAliasName);
        $indexNameBeforeMigration = $this->getIndexNameFromAlias($client, $recordAliasName);

        self::assertEquals(2, $recordsInIndexBeforeMigration);
        self::assertNotNull($indexNameBeforeMigration);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $indexNameAfterMigration = $this->getIndexNameFromAlias($client, $recordAliasName);
        self::assertEquals($indexNameBeforeMigration, $indexNameAfterMigration);
    }

    public function test_it_reindex_record_when_launched_in_non_sass_version(): void
    {
        if ($this->isSassVersion()) {
            $this->markTestSkipped('As version provider cannot be mocked, this test can only be launched on non sass version');
        }

        $recordAliasName = $this->getParameter('record_index_name');
        $client = $this->getClient();
        $this->loadReferenceEntityAndRecords();

        $recordsInIndexBeforeMigration = $this->getRecordsCountInIndex($client, $recordAliasName);
        $indexNameBeforeMigration = $this->getIndexNameFromAlias($client, $recordAliasName);

        self::assertEquals(2, $recordsInIndexBeforeMigration);
        self::assertNotNull($indexNameBeforeMigration);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $indexNameAfterMigration = $this->getIndexNameFromAlias($client, $recordAliasName);
        self::assertEquals(2, $recordsInIndexBeforeMigration);
        self::assertNotEquals($indexNameBeforeMigration, $indexNameAfterMigration);
    }

    private function isSassVersion(): bool
    {
        $versionProvider = self::$container->get('pim_catalog.version_provider');

        return $versionProvider->isSaaSVersion();
    }

    private function getClient(): Client
    {
        $clientBuilder = $this->get('akeneo_elasticsearch.client_builder');
        $hosts = $this->getParameter('index_hosts');
        $hosts = is_string($hosts) ? [$hosts] : $hosts;

        return $clientBuilder->setHosts($hosts)->build();
    }

    private function getIndexNameFromAlias(Client $client, string $aliasName): ?string
    {
        $indices = $client->indices();
        $aliases = $indices->getAlias(['name' => $aliasName]);

        return array_keys($aliases)[0] ?? null;
    }

    private function loadReferenceEntityAndRecords(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO akeneo_reference_entity_reference_entity(identifier, labels, image, attribute_as_label, attribute_as_image)
VALUES
	('city', '[]', NULL, NULL, NULL)
SQL
        );

        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO akeneo_reference_entity_record(identifier, code, reference_entity_identifier, value_collection)
VALUES
	('city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717', 'paris', 'city', '{}'),
	('city_nantes_bf11a6b3-3e46-4bbf-b35c-814a0020c717', 'nantes', 'city', '{}')
SQL
        );

        $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer')->indexByReferenceEntity(
            ReferenceEntityIdentifier::fromString('city')
        );
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();
    }

    private function getRecordsCountInIndex(Client $client, string $aliasName): int
    {
        $response = $client->count(['index' => $aliasName]);

        return $response['count'];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
