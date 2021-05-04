<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\UI\Query;

use Akeneo\Test\Integration\TestCase;

final class CountEnterpriseSettingsEntitiesQueryIntegration extends TestCase
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
            'count_rules' => 2,
        ];

        $this->assertEqualsCanonicalizing($expectedCounts, $result);
    }
}
