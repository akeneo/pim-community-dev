<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\DatabasePurger;

use Akeneo\Platform\Installer\Domain\Service\DatabasePurgerInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalPurgerTest extends TestCase
{
    /**
     * @test
     */
    public function it_remove_all_data_in_the_given_tables()
    {
        $this->assertNotEmpty($this->getAssociationTypeCodes());
        $this->getPurger()->purge(['pim_catalog_association_type']);
        $this->assertEmpty($this->getAssociationTypeCodes());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getPurger(): DatabasePurgerInterface
    {
        return $this->get('Akeneo\Platform\Installer\Infrastructure\DatabasePurger\DbalPurger');
    }

    private function getAssociationTypeCodes(): array
    {
        return $this->getConnection()->executeQuery('SELECT code FROM pim_catalog_association_type')->fetchFirstColumn();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
