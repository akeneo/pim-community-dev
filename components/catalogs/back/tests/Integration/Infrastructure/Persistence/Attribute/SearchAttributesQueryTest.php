<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Infrastructure\Persistence\Attribute\SearchAttributesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Attribute\SearchAttributesQuery
 */
class SearchAttributesQueryTest extends IntegrationTestCase
{
    private ?SearchAttributesQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->loadAttributeGroups();

        $this->query = self::getContainer()->get(SearchAttributesQuery::class);
    }

    public function testItGetsAllowedAttributesTypes(): void
    {
        $this->loadAttributes();

        $result = $this->query->execute(
            null,
            1,
            100,
            [
                'pim_catalog_identifier',
                'pim_catalog_text',
                'pim_catalog_textarea',
                'pim_catalog_simpleselect',
                'pim_catalog_multiselect',
                'pim_catalog_number',
                'pim_catalog_metric',
                'pim_catalog_boolean',
                'pim_catalog_date'
            ]
        );

        $this->assertEquals([
            [
                'code' => 'name',
                'label' => '[name]',
                'type' => 'pim_catalog_text',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'marketing',
                'attribute_group_label' => '[marketing]',
            ],
            [
                'code' => 'description',
                'label' => '[description]',
                'type' => 'pim_catalog_textarea',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'marketing',
                'attribute_group_label' => '[marketing]',
            ],
            [
                'code' => 'materials',
                'label' => '[materials]',
                'type' => 'pim_catalog_multiselect',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'marketing',
                'attribute_group_label' => '[marketing]',
            ],
            [
                'code' => 'clothing_size',
                'label' => '[clothing_size]',
                'type' => 'pim_catalog_simpleselect',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'marketing',
                'attribute_group_label' => '[marketing]',
            ],
            [
                'code' => 'number_battery_cells',
                'label' => '[number_battery_cells]',
                'type' => 'pim_catalog_number',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'technical',
                'attribute_group_label' => '[technical]',
            ],
            [
                'code' => 'certified',
                'label' => '[certified]',
                'type' => 'pim_catalog_boolean',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'technical',
                'attribute_group_label' => '[technical]',
            ],
            [
                'code' => 'released_at',
                'label' => '[released_at]',
                'type' => 'pim_catalog_date',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'technical',
                'attribute_group_label' => '[technical]',
            ],
            [
                'code' => 'weight',
                'label' => '[weight]',
                'type' => 'pim_catalog_metric',
                'scopable' => false,
                'localizable' => false,
                'measurement_family' => 'Weight',
                'default_measurement_unit' => 'KILOGRAM',
                'attribute_group_code' => 'technical',
                'attribute_group_label' => '[technical]',
            ],
            [
                'code' => 'sku',
                'label' => '[sku]',
                'type' => 'pim_catalog_identifier',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'other',
                'attribute_group_label' => '[other]',
            ],
        ], $result);
    }

    public function testItSearchesAttributesByName(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->createAttribute([
            'code' => 'description',
            'type' => 'pim_catalog_text',
            'group' => 'marketing',
        ]);

        $result = $this->query->execute('desc');

        $this->assertEquals([
            [
                'code' => 'description',
                'label' => '[description]',
                'type' => 'pim_catalog_text',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'marketing',
                'attribute_group_label' => '[marketing]',
            ]
        ], $result);
    }

    public function testItGetsAttributesByTypes(): void
    {
        $this->loadAttributes();

        $result = $this->query->execute(null, 1, 100, ['pim_catalog_text','pim_catalog_simpleselect']);

        $this->assertEquals([
            [
                'code' => 'name',
                'label' => '[name]',
                'type' => 'pim_catalog_text',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'marketing',
                'attribute_group_label' => '[marketing]',
            ],
            [
                'code' => 'clothing_size',
                'label' => '[clothing_size]',
                'type' => 'pim_catalog_simpleselect',
                'scopable' => false,
                'localizable' => false,
                'attribute_group_code' => 'marketing',
                'attribute_group_label' => '[marketing]',
        ],
        ], $result);
    }

    private function loadAttributeGroups(): void
    {
        // there is already an attribute "other" (sort order 0) in the minimal catalog
        $this->createAttributeGroup(['sort_order' => 1, 'code' => 'marketing']);
        $this->createAttributeGroup(['sort_order' => 2, 'code' => 'technical']);
    }

    private function loadAttributes(): void
    {
        // there is already an attribute "pim_catalog_identifier" in the minimal catalog

        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'group' => 'marketing',
        ]);
        $this->createAttribute([
            'code' => 'description',
            'type' => 'pim_catalog_textarea',
            'group' => 'marketing',
        ]);
        $this->createAttribute([
            'code' => 'materials',
            'type' => 'pim_catalog_multiselect',
            'group' => 'marketing',
        ]);
        $this->createAttribute([
            'code' => 'clothing_size',
            'type' => 'pim_catalog_simpleselect',
            'group' => 'marketing',
        ]);
        $this->createAttribute([
            'code' => 'price',
            'type' => 'pim_catalog_price_collection',
            'group' => 'other',
        ]);
        $this->createAttribute([
            'code' => 'number_battery_cells',
            'type' => 'pim_catalog_number',
            'group' => 'technical',
        ]);
        $this->createAttribute([
            'code' => 'certified',
            'type' => 'pim_catalog_boolean',
            'group' => 'technical',
        ]);
        $this->createAttribute([
            'code' => 'released_at',
            'type' => 'pim_catalog_date',
            'group' => 'technical',
        ]);
        $this->createAttribute([
            'code' => 'notice',
            'type' => 'pim_catalog_file',
            'group' => 'technical'
        ]);
        $this->createAttribute([
            'code' => 'picture',
            'type' => 'pim_catalog_image',
            'group' => 'marketing',
        ]);
        $this->createAttribute([
            'code' => 'weight',
            'type' => 'pim_catalog_metric',
            'metric_family' => 'Weight',
            'default_metric_unit' => 'KILOGRAM',
            'group' => 'technical',
        ]);
    }
}
