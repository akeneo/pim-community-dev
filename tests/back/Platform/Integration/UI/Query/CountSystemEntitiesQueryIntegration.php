<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\UI\Query;

use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountSystemEntitiesQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @group ce
     */
    public function test_it_count_the_number_of_each_settings_entities()
    {
        $result = $this->get('akeneo.pim_ui.query.count_system_entities_query')->execute();

        $expectedCounts = [
          'count_users' => 4,
          'count_user_groups' => 3,
          'count_roles' => 4,
          'count_product_values' => 0,
        ];

        $this->assertEqualsCanonicalizing($expectedCounts, array_intersect_assoc($result, $expectedCounts));
    }
}
