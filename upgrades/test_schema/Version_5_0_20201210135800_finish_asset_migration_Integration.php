<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client as NativeClient;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class Version_5_0_20201210135800_finish_asset_migration_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20201210135800_finish_asset_migration';

    private Client $assetClient;
    private Client $temporaryClient;
    private NativeClient $nativeClient;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetClient = $this->get('akeneo_assetmanager.client.asset');

        // Recreate the temporary service because we don't have it anymore in the DI.
        $configurationLoader = new Loader(
            $this->getParameter('asset_index_configuration'),
            $this->get(ParameterBagInterface::class)
        );
        $this->temporaryClient = new Client(
            $this->get('akeneo_elasticsearch.client_builder'),
            $configurationLoader,
            [$this->getParameter('index_hosts')],
            sprintf('%s_temporary', $this->getParameter('asset_index_name')),
            ''
        );

        $clientBuilder = $this->get('akeneo_elasticsearch.client_builder')->setHosts([
            $this->getParameter('index_hosts')
        ]);
        $this->nativeClient = $clientBuilder->build();
    }

    /** @test */
    public function it_switches_the_asset_alias_and_cleanup(): void
    {
        $assetIndexBeforeMigration = $this->getIndexNameFromAlias($this->assetClient->getIndexName());
        $this->assertNotNull($assetIndexBeforeMigration);
        $this->temporaryClient->resetIndex();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $assetIndexAfterMigration = $this->getIndexNameFromAlias($this->assetClient->getIndexName());
        $this->assertNotNull($assetIndexAfterMigration);
        $this->assertNotEquals($assetIndexBeforeMigration, $assetIndexAfterMigration);

        self::assertTrue($this->assetClient->hasIndexForAlias());
        self::assertFalse($this->temporaryClient->hasIndexForAlias());
    }

    private function getIndexNameFromAlias(string $aliasName): ?string
    {
        $indices = $this->nativeClient->indices();
        $aliases = $indices->getAlias(['name' => $aliasName]);

        return array_keys($aliases)[0] ?? null;
    }
}
