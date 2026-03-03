<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\UI\Query;

use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountSettingsEntitiesQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_count_the_number_of_each_settings_entities()
    {
        $result = $this->get('akeneo.pim_ui.query.count_settings_entities_query')->execute();

        $expectedCounts = [
          'count_categories' => 7,
          'count_category_trees' => 2,
          'count_channels' => 3,
          'count_locales' => 4,
          'count_currencies' => 3,
          'count_attribute_groups' => 4,
          'count_attributes' => 30,
          'count_families' => 4,
          'count_measurements' => 23,
          'count_association_types' => 4,
          'count_group_types' => 1,
          'count_groups' => 2,
          'count_identifier_generators' => 0,
        ];

        $this->assertEqualsCanonicalizing($expectedCounts, array_intersect_assoc($result, $expectedCounts));
    }
}
