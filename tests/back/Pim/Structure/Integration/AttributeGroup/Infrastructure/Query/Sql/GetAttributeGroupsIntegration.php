<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup\Infrastructure\Query\Sql;

use Akeneo\Pim\Structure\Bundle\Infrastructure\Query\Sql\GetAttributeGroups;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class GetAttributeGroupsIntegration extends TestCase
{
    public function testItReturnsAttributeGroups()
    {
        $attributeGroupId1 = $this->createAttributeGroup('an_attribute_group', 1, [], true);
        $this->createAttributeGroup('another_attribute_group', 2, ['en_US' => 'Another attribute group'], false);

        $this->createAttribute('nice_attribute1', 'pim_catalog_text', $attributeGroupId1);
        $this->createAttribute('nice_attribute2', 'pim_catalog_text', $attributeGroupId1);

        $this->assertSame([
            [
                'code' => 'an_attribute_group',
                'sort_order' => 1,
                'labels' => [],
                'attribute_count' => 2,
            ],
            [
                'code' => 'another_attribute_group',
                'sort_order' => 2,
                'labels' => [
                    'en_US' => 'Another attribute group',
                ],
                'attribute_count' => 0,
            ],
            [
                'code' => 'other',
                'sort_order' => 100,
                'labels' => [
                    'en_US' => 'Other',
                    'fr_FR' => 'Autre',
                ],
                'attribute_count' => 1,
            ],
        ], $this->getQuery()->all());
    }

    public function testItReturnsAttributeGroupsWithDqiIsActivatedWhenFeatureFlagIsEnabled()
    {
        $this->get('feature_flags')->enable('data_quality_insights');

        $attributeGroupId1 = $this->createAttributeGroup('an_attribute_group', 1, [], true);
        $this->createAttributeGroup('another_attribute_group', 2, ['en_US' => 'Another attribute group'], false);

        $this->createAttribute('nice_attribute1', 'pim_catalog_text', $attributeGroupId1);
        $this->createAttribute('nice_attribute2', 'pim_catalog_text', $attributeGroupId1);

        $this->assertSame([
            [
                'code' => 'an_attribute_group',
                'sort_order' => 1,
                'labels' => [],
                'attribute_count' => 2,
                'is_dqi_activated' => true,
            ],
            [
                'code' => 'another_attribute_group',
                'sort_order' => 2,
                'labels' => [
                    'en_US' => 'Another attribute group',
                ],
                'attribute_count' => 0,
                'is_dqi_activated' => false,
            ],
            [
                'code' => 'other',
                'sort_order' => 100,
                'labels' => [
                    'en_US' => 'Other',
                    'fr_FR' => 'Autre',
                ],
                'attribute_count' => 1,
                'is_dqi_activated' => true,
            ],
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

    private function createAttributeGroup(string $code, int $sortOrder, array $labels, ?bool $isDqiActivated): int
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $attributeGroupSql = <<<SQL
INSERT INTO pim_catalog_attribute_group (code, sort_order, created, updated) VALUES (:code, :sort_order, NOW(), NOW());
SQL;

        $connection->executeQuery($attributeGroupSql, ['code' => $code, 'sort_order' => $sortOrder]);
        $attributeGroupId = (int) $connection->lastInsertId();

        $attributeGroupTranslationSql = <<<SQL
INSERT INTO pim_catalog_attribute_group_translation (foreign_key, label, locale) VALUES (:attribute_group_id, :label, :locale);
SQL;

        foreach ($labels as $locale => $label) {
            $connection->executeQuery($attributeGroupTranslationSql, ['attribute_group_id' => $attributeGroupId, 'label' => $label, 'locale' => $locale]);
        }

        if (null !== $isDqiActivated) {
            $attributeGroupActivationSql = <<<SQL
INSERT INTO pim_data_quality_insights_attribute_group_activation (attribute_group_code, activated, updated_at) VALUES (:attribute_group_code, :is_dqi_activated, NOW());
SQL;
            $connection->executeQuery($attributeGroupActivationSql, ['attribute_group_code' => $code, 'is_dqi_activated' => $isDqiActivated], ['is_dqi_activated' => Types::BOOLEAN]);
        }

        return $attributeGroupId;
    }

    private function createAttribute(string $code, string $attributeType, int $attributeGroupId): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $attributeSql = <<<SQL
            INSERT INTO `pim_catalog_attribute` ( `group_id`, `sort_order`, `is_required`, `is_unique`, `is_localizable`, `is_scopable`, `code`, `entity_type`, `attribute_type`, `backend_type`, `created`, `updated`)
            VALUES (:attribute_group_id, 1, 1, 1, 1, 0, :code, 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', :attribute_type, 'text', NOW(), NOW());
        SQL;
        $connection->executeQuery($attributeSql, [
            'code' => $code,
            'attribute_type' => $attributeType,
            'attribute_group_id' => $attributeGroupId,
        ]);
    }
}
