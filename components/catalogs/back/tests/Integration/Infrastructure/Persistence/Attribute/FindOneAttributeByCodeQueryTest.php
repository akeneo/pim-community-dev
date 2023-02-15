<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Infrastructure\Persistence\Attribute\FindOneAttributeByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Attribute\FindOneAttributeByCodeQuery
 */
class FindOneAttributeByCodeQueryTest extends IntegrationTestCase
{
    private ?FindOneAttributeByCodeQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(FindOneAttributeByCodeQuery::class);
    }

    public function testItReturnsTheNormalizedAttribute(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);

        $result = $this->query->execute('name');

        $this->assertEquals([
            'code' => 'name',
            'label' => '[name]',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
            'attribute_group_code' => 'other',
            'attribute_group_label' => '[other]',
        ], $result);
    }

    public function testItReturnsTheNormalizedAttributeWithMeasurementFamilyAndDefaultMeasurementUnit(): void
    {
        $this->createAttribute([
            'code' => 'weight',
            'type' => 'pim_catalog_metric',
            'metric_family' => 'Weight',
            'default_metric_unit' => 'KILOGRAM',
        ]);

        $result = $this->query->execute('weight');

        $this->assertEquals([
            'code' => 'weight',
            'label' => '[weight]',
            'type' => 'pim_catalog_metric',
            'scopable' => false,
            'localizable' => false,
            'attribute_group_code' => 'other',
            'attribute_group_label' => '[other]',
            'measurement_family' => 'Weight',
            'default_measurement_unit' => 'KILOGRAM',
        ], $result);
    }

    public function testItReturnsCategoriesSystemAttribute(): void
    {
        $result = $this->query->execute('categories');

        $this->assertEquals([
            'code' => 'categories',
            'type' => 'categories',
            'scopable' => false,
            'localizable' => true,
            'attribute_group_code' => 'system',
            'attribute_group_label' => 'System',
            'label' => 'Categories',
        ], $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        $result = $this->query->execute('unknown');

        $this->assertNull($result);
    }
}
