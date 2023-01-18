<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup\Infrastructure\Query\Sql;

use Akeneo\Pim\Structure\Bundle\infrastructure\Query\Sql\GetAttributeGroups;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class SqlGetAttributeGroupsIntegration extends TestCase
{
    public function testItReturnsAttributeGroups() {
        $this->createAttributeGroup('an_attribute_group', 1, []);
        $this->createAttributeGroup('another_attribute_group', 2, ['en_US' => 'Another attribute group']);

        $this->assertSame([
            [
                'code' => 'an_attribute_group',
                'sort_order' => 1,
                'labels' => [],
            ],
            [
                'code' => 'another_attribute_group',
                'sort_order' => 2,
                'labels' => [
                    'en_US' => 'Another attribute group',
                ],
            ],
            [
                'code' => 'other',
                'sort_order' => 100,
                'labels' => [
                    'en_US' => 'Other',
                    'fr_FR' => 'Autre',
                ],
            ]
        ], $this->getQuery()->all());
    }

    public function testItReturnsAttributeGroupsWithDqiIsActivatedWhenFeatureFlagIsEnabled() {
        $this->get('feature_flags')->enable('data_quality_insights');

        $this->createAttributeGroup('an_attribute_group', 1, []);
        $this->createAttributeGroup('another_attribute_group', 2, ['en_US' => 'Another attribute group']);

        $this->assertSame([
            [
                'code' => 'an_attribute_group',
                'sort_order' => 1,
                'labels' => [],
                'is_dqi_activated' => true,
            ],
            [
                'code' => 'another_attribute_group',
                'sort_order' => 2,
                'labels' => [
                    'en_US' => 'Another attribute group',
                ],
                'is_dqi_activated' => true,
            ],
            [
                'code' => 'other',
                'sort_order' => 100,
                'labels' => [
                    'en_US' => 'Other',
                    'fr_FR' => 'Autre',
                ],
                'is_dqi_activated' => true,
            ]
        ], $this->getQuery()->all());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): GetAttributeGroups
    {
        return $this->get('Akeneo\Pim\Structure\Bundle\Infrastructure\Query\Sql\GetAttributeGroups');
    }

    private function createAttributeGroup(string $code, int $sortOrder, array $labels): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $attributeGroupSql = <<<SQL
INSERT INTO pim_catalog_attribute_group (code, sort_order, created, updated) VALUES (:code, :sort_order, NOW(), NOW());
SQL;

        $connection->executeQuery($attributeGroupSql, ['code' => $code, 'sort_order' => $sortOrder]);
        $attributeGroupId = $connection->lastInsertId();

        $attributeGroupTranslationSql = <<<SQL
INSERT INTO pim_catalog_attribute_group_translation (foreign_key, label, locale) VALUES (:attribute_group_id, :label, :locale);
SQL;

        foreach ($labels as $locale => $label) {
            $connection->executeQuery($attributeGroupTranslationSql, ['attribute_group_id' => $attributeGroupId, 'label' => $label, 'locale' => $locale]);
        }
    }
}
