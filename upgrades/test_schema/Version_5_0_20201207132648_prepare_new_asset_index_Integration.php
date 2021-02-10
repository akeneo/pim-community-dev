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

use Akeneo\AssetManager\Infrastructure\Symfony\Command\IndexAllAssetsOnTemporaryIndexCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class Version_5_0_20201207132648_prepare_new_asset_index_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Client $temporaryClient;
    private Connection $connection;

    private const MIGRATION_LABEL = '_5_0_20201207132648_prepare_new_asset_index';

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->temporaryClient = $this->get('akeneo_assetmanager.client.asset_temporary');
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_creates_new_asset_index()
    {
        try {
            $this->temporaryClient->deleteIndex();
        } catch (\Exception $e) {
            // Already deleted
        }

        Assert::false($this->temporaryClient->hasIndexForAlias());
        Assert::false($this->doesConfigurationExist());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::true($this->temporaryClient->hasIndexForAlias());
        Assert::true($this->doesConfigurationExist());
    }

    private function doesConfigurationExist(): bool
    {
        $sql = <<<SQL
SELECT EXISTS(
    SELECT 1 FROM pim_configuration WHERE code = :code
) as is_existing
SQL;
        $statement = $this->connection->executeQuery($sql, [
            'code' => IndexAllAssetsOnTemporaryIndexCommand::CONFIGURATION_CODE,
        ]);

        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }
}
