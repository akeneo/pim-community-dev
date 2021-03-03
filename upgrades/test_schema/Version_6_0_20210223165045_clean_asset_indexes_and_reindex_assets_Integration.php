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

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Elasticsearch\Client;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;
use Webmozart\Assert\Assert;

final class Version_6_0_20210223165045_clean_asset_indexes_and_reindex_assets_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210223165045_clean_asset_indexes_and_reindex_assets';

    private Client $nativeClient;

    protected function setUp(): void
    {
        parent::setUp();
        $clientBuilder = $this->get('akeneo_elasticsearch.client_builder')->setHosts([
            $this->getParameter('index_hosts')
        ]);
        $this->nativeClient = $clientBuilder->build();
    }

    public function test_it_removes_temporary_index(): void
    {
        $temporaryIndexName = sprintf('%s_temporary', $this->getParameter('asset_index_name'));
        $this->cleanTemporaryIndex($temporaryIndexName);

        $this->nativeClient->indices()->create(['index' => $temporaryIndexName]);
        Assert::true($this->nativeClient->indices()->exists(['index' => $temporaryIndexName]));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->nativeClient->indices()->refresh(['index' => '_all']);
        Assert::false($this->nativeClient->indices()->exists(['index' => $temporaryIndexName]));
    }

    public function test_it_removes_temporary_alias(): void
    {
        $temporaryAliasName = sprintf('%s_temporary', $this->getParameter('asset_index_name'));
        $temporaryIndexName = sprintf('%s_index', $temporaryAliasName);
        $this->cleanTemporaryIndex($temporaryAliasName);
        $this->cleanTemporaryIndex($temporaryIndexName);

        $this->nativeClient->indices()->create(['index' => $temporaryIndexName]);
        $this->nativeClient->indices()->putAlias(['index' => $temporaryIndexName, 'name' => $temporaryAliasName]);
        Assert::true($this->nativeClient->indices()->exists(['index' => $temporaryIndexName]));
        Assert::true($this->nativeClient->indices()->existsAlias(['index' => $temporaryIndexName, 'name' => $temporaryAliasName]));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->nativeClient->indices()->refresh(['index' => '_all']);
        Assert::false($this->nativeClient->indices()->exists(['index' => $temporaryIndexName]));
        Assert::false($this->nativeClient->indices()->existsAlias(['index' => $temporaryIndexName, 'name' => $temporaryAliasName]));
    }

    private function cleanTemporaryIndex(string $temporaryIndexName): void
    {
        $indices = $this->nativeClient->indices();
        if ($indices->existsAlias(['name' => $temporaryIndexName])) {
            $aliases = $indices->getAlias(['name' => $temporaryIndexName]);
            $temporaryIndexName = array_keys($aliases)[0];
        }

        if ($indices->exists(['index' => $temporaryIndexName])) {
            $result = $indices->delete(['index' => $temporaryIndexName]);
            Assert::true($result['acknowledged']);
        }

        $indices->refresh(['index' => '_all']);
        Assert::false($indices->exists(['index' => $temporaryIndexName]));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
