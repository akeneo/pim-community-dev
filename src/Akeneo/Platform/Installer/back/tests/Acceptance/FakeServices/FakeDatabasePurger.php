<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Test\Acceptance\FakeServices;

use Akeneo\Platform\Installer\Domain\Service\DatabasePurgerInterface;
use PHPUnit\Framework\Assert;

class FakeDatabasePurger implements DatabasePurgerInterface
{
    private array $tablePurged = [];

    public function purge(array $tableToReset): void
    {
        $this->tablePurged = $tableToReset;
    }

    public function assertTablesHaveBeenPurged(array $tablesName): void
    {
        Assert::assertEqualsCanonicalizing($this->tablePurged, $tablesName, 'Failed asserting that tables have been purged.');
    }

    public function assertTablesHaveNotBeenPurged(array $tablesName): void
    {
        foreach ($tablesName as $tableName) {
            if (in_array($tableName, $this->tablePurged)) {
                Assert::fail("Failed asserting that table $tableName have not been purged.");
            }
        }
    }
}
