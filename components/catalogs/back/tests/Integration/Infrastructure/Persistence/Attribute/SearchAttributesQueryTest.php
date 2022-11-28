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
                'identifier',
                'text',
                'textarea',
                'simpleselect',
                'multiselect',
                'number',
                'metric',
                'boolean',
                'date'
            ]
        );

        $this->assertEquals([
            [
                'code' => 'sku',
                'label' => '[sku]',
                'type' => 'pim_catalog_identifier',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'name',
                'label' => '[name]',
                'type' => 'pim_catalog_text',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'description',
                'label' => '[description]',
                'type' => 'pim_catalog_textarea',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'materials',
                'label' => '[materials]',
                'type' => 'pim_catalog_multiselect',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'clothing_size',
                'label' => '[clothing_size]',
                'type' => 'pim_catalog_simpleselect',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'number_battery_cells',
                'label' => '[number_battery_cells]',
                'type' => 'pim_catalog_number',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'certified',
                'label' => '[certified]',
                'type' => 'pim_catalog_boolean',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'released_at',
                'label' => '[released_at]',
                'type' => 'pim_catalog_date',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'weight',
                'label' => '[weight]',
                'type' => 'pim_catalog_metric',
                'scopable' => false,
                'localizable' => false,
                'measurement_family' => 'Weight',
                'default_measurement_unit' => 'KILOGRAM',
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
        ]);

        $result = $this->query->execute('desc');

        $this->assertEquals([
            [
                'code' => 'description',
                'label' => '[description]',
                'type' => 'pim_catalog_text',
                'scopable' => false,
                'localizable' => false,
            ]
        ], $result);
    }

    public function testItGetsAttributesByTypes(): void
    {
        $this->loadAttributes();

        $result = $this->query->execute(null, 1, 100, ['text','simpleselect']);

        $this->assertEquals([
            [
                'code' => 'name',
                'label' => '[name]',
                'type' => 'pim_catalog_text',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'clothing_size',
                'label' => '[clothing_size]',
                'type' => 'pim_catalog_simpleselect',
                'scopable' => false,
                'localizable' => false,
            ],
        ], $result);
    }

    private function loadAttributes(): void
    {
        // there is already an attribute "pim_catalog_identifier" in the minimal catalog

        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->createAttribute([
            'code' => 'description',
            'type' => 'pim_catalog_textarea',
        ]);
        $this->createAttribute([
            'code' => 'materials',
            'type' => 'pim_catalog_multiselect',
        ]);
        $this->createAttribute([
            'code' => 'clothing_size',
            'type' => 'pim_catalog_simpleselect',
        ]);
        $this->createAttribute([
            'code' => 'price',
            'type' => 'pim_catalog_price_collection',
        ]);
        $this->createAttribute([
            'code' => 'number_battery_cells',
            'type' => 'pim_catalog_number',
        ]);
        $this->createAttribute([
            'code' => 'certified',
            'type' => 'pim_catalog_boolean',
        ]);
        $this->createAttribute([
            'code' => 'released_at',
            'type' => 'pim_catalog_date',
        ]);
        $this->createAttribute([
            'code' => 'notice',
            'type' => 'pim_catalog_file',
        ]);
        $this->createAttribute([
            'code' => 'picture',
            'type' => 'pim_catalog_image',
        ]);
        $this->createAttribute([
            'code' => 'weight',
            'type' => 'pim_catalog_metric',
            'metric_family' => 'Weight',
            'default_metric_unit' => 'KILOGRAM',
        ]);
    }
}
