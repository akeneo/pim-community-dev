<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\UI\Query;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountSystemEntitiesQueryIntegration extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @group ce
     */
    public function test_it_count_the_number_of_each_settings_entities()
    {
        $this->addUserGroup('appUserGroup', 'app');
        $this->addRole('appRole', 'app');

        $result = $this->get('akeneo.pim_ui.query.count_system_entities_query')->execute();

        $expectedCounts = [
          'count_users' => 4,
          'count_user_groups' => 3,
          'count_roles' => 4,
          'count_product_values' => 0,
        ];

        $this->assertEqualsCanonicalizing($expectedCounts, array_intersect_assoc($result, $expectedCounts));
    }

    private function addUserGroup(string $name, string $type='default'): void
    {
        $this->connection->insert('oro_access_group', [
            'name' => $name,
            'type' => $type,
        ]);
    }

    private function addRole(string $name, string $type='default'): void
    {
        $this->connection->insert('oro_access_role', [
            'role' => $name,
            'label' => $name,
            'type' => $type,
        ]);
    }

}
