<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute\SqlCountAttributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SqlCountAttributesIntegration extends TestCase
{
    private SqlCountAttributes $sqlCountAttributes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlCountAttributes = $this->get('Akeneo\Pim\Structure\Component\Query\InternalApi\CountAttributes');
        $this->createAttribute('name');
        $this->createAttribute('description');
        $this->createAttribute('length');
    }

    public function test_it_counts_attributes(): void
    {
        $this->assertEquals(4, $this->sqlCountAttributes->byCodes([], []));
        $this->assertEquals(1, $this->sqlCountAttributes->byCodes(['sku'], []));
        $this->assertEquals(4, $this->sqlCountAttributes->byCodes(['name', 'description', 'sku', 'length'], []));
        $this->assertEquals(1, $this->sqlCountAttributes->byCodes(['name', 'description'], ['name', 'sku']));
        $this->assertEquals(3, $this->sqlCountAttributes->byCodes([], ['name']));
        $this->assertEquals(0, $this->sqlCountAttributes->byCodes([], ['sku', 'name', 'description', 'length']));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(string $code): void
    {
        $attributeGroupSql = <<<SQL
SELECT id from pim_catalog_attribute_group LIMIT 0, 1;
SQL;

        $attributeGroupId = $this->get('database_connection')->executeQuery($attributeGroupSql)->fetchOne();

        $attributeSql = <<<SQL
INSERT INTO pim_catalog_attribute (group_id, sort_order, is_required, is_unique, is_localizable, is_scopable, code, entity_type, attribute_type, backend_type, created, updated) 
VALUES (:attribute_group_id, 1, 1, 1, 1, 1, :code, 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', 'pim_catalog_text', 'test', NOW(), NOW())
SQL;

        $this->get('database_connection')->executeQuery($attributeSql, ['code' => $code, 'attribute_group_id' => $attributeGroupId]);
    }
}
