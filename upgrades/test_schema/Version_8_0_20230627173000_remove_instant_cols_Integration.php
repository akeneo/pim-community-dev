<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_8_0_20230627173000_remove_instant_cols_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230627173000_remove_instant_cols';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /**
     * To reproduce the scenario we should be in MySQL < 8.0.29 and then upgrade to 8.0.30,
     * as it's impossible we just test that the migration can be launched
     */
    public function testItLaunchOptimizeOnSeveralTable()
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
