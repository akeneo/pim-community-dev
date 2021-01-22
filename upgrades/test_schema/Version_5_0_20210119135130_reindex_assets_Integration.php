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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Elasticsearch\Client;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20210119135130_reindex_assets_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20210119135130_reindex_assets';

    /** @test */
    public function it_reindex_all_assets_using_a_new_index()
    {
        $assetAliasName = $this->getParameter('asset_index_name');
        $client = $this->getClient();

        $this->loadAssetFamilyAndAsset();

        self::assertGreaterThan(0, $this->getAssetsCountInIndex($client, $assetAliasName));
        $indexNameBeforeMigration = $this->getIndexNameFromAlias($client, $assetAliasName);
        self::assertNotNull($indexNameBeforeMigration);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $indexNameAfterMigration = $this->getIndexNameFromAlias($client, $assetAliasName);
        self::assertNotNull($indexNameBeforeMigration);
        self::assertNotEquals($indexNameBeforeMigration, $indexNameAfterMigration);
        self::assertGreaterThan(0, $this->getAssetsCountInIndex($client, $assetAliasName));
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

    private function loadAssetFamilyAndAsset(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO akeneo_asset_manager_asset_family (identifier, labels, image, attribute_as_label, attribute_as_main_media, rule_templates, naming_convention, transformations)
VALUES
	('packshot', '[]', NULL, NULL, NULL, '[]', '[]', '[]');
SQL
        );

        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO akeneo_asset_manager_asset (identifier, code, asset_family_identifier, value_collection)
VALUES
	('identifier1', 'code1', 'packshot', '{}');
SQL
        );

        $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset_indexer')->indexByAssetFamily(
            AssetFamilyIdentifier::fromString('packshot')
        );
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();
    }

    private function getAssetsCountInIndex(Client $client, string $aliasName): int
    {
        $countResult = $client->count([
            'index' => $aliasName,
        ]);

        return $countResult['count'];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
