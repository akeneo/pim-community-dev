<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\Query;

use Akeneo\Platform\Installer\Infrastructure\Query\FindTables;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindTablesTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_tables(): void
    {
        $tables = $this->getQuery()->all();

        $this->assertContainsAtLeast(['oro_user', 'pim_catalog_product', 'pim_catalog_attribute'], $tables);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): FindTables
    {
        return $this->get('Akeneo\Platform\Installer\Infrastructure\Query\FindTables');
    }

    private function assertContainsAtLeast(array $expectedTable, array $actualTable): void
    {
        $tablesNotFound = array_diff($expectedTable, $actualTable);

        $this->assertEmpty(
            $tablesNotFound,
            sprintf('The following tables was not found: %s', implode(',', $tablesNotFound)),
        );
    }
}
