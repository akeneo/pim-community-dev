<?php

namespace AkeneoTest\Pim\Structure\Integration\Query\InternalApi\AttributeOption;

use Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeOption\SqlFindAttributeOptions;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class SqlFindAttributeOptionsIntegration extends TestCase
{
    public function test_it_paginates_options(): void
    {
        $query = $this->getQuery();

        Assert::assertSame($query->search('attribute1', '', 1, 5), [
            ['code' => 'option1', 'labels' => ['en_US' => 'Option one']],
            ['code' => 'option2', 'labels' => ['en_US' => '[option2]']],
            ['code' => 'option3', 'labels' => ['en_US' => 'Option three']],
            ['code' => 'option4', 'labels' => ['en_US' => 'Foobar']],
            ['code' => 'option5', 'labels' => ['en_US' => '[option5]']],
        ]);

        Assert::assertSame($query->search('attribute1', '', 2, 5), [
            ['code' => 'option6', 'labels' => ['en_US' => '[option6]']],
            ['code' => 'option7', 'labels' => ['en_US' => '[option7]']],
            ['code' => 'option8', 'labels' => ['en_US' => '[option8]']],
            ['code' => 'option9', 'labels' => ['en_US' => '[option9]']],
            ['code' => 'option10', 'labels' => ['en_US' => '[option10]']],
        ]);

        Assert::assertSame($query->search('attribute1', '', 5, 5), [
            ['code' => 'option21', 'labels' => ['en_US' => '[option21]']],
            ['code' => 'option22', 'labels' => ['en_US' => '[option22]']],
        ]);

        Assert::assertSame($query->search('attribute1', '', 6, 5), []);
    }

    public function test_it_without_paginates_options(): void
    {
        $query = $this->getQuery();

        Assert::assertSame($query->search('attribute1', '', 1, -1), [
            ['code' => 'option1', 'labels' => ['en_US' => 'Option one']],
            ['code' => 'option2', 'labels' => ['en_US' => '[option2]']],
            ['code' => 'option3', 'labels' => ['en_US' => 'Option three']],
            ['code' => 'option4', 'labels' => ['en_US' => 'Foobar']],
            ['code' => 'option5', 'labels' => ['en_US' => '[option5]']],
            ['code' => 'option6', 'labels' => ['en_US' => '[option6]']],
            ['code' => 'option7', 'labels' => ['en_US' => '[option7]']],
            ['code' => 'option8', 'labels' => ['en_US' => '[option8]']],
            ['code' => 'option9', 'labels' => ['en_US' => '[option9]']],
            ['code' => 'option10', 'labels' => ['en_US' => '[option10]']],
            ['code' => 'option11', 'labels' => ['en_US' => '[option11]']],
            ['code' => 'option12', 'labels' => ['en_US' => '[option12]']],
            ['code' => 'option13', 'labels' => ['en_US' => '[option13]']],
            ['code' => 'option14', 'labels' => ['en_US' => '[option14]']],
            ['code' => 'option15', 'labels' => ['en_US' => '[option15]']],
            ['code' => 'option16', 'labels' => ['en_US' => '[option16]']],
            ['code' => 'option17', 'labels' => ['en_US' => '[option17]']],
            ['code' => 'option18', 'labels' => ['en_US' => '[option18]']],
            ['code' => 'option19', 'labels' => ['en_US' => '[option19]']],
            ['code' => 'option20', 'labels' => ['en_US' => '[option20]']],
            ['code' => 'option21', 'labels' => ['en_US' => '[option21]']],
            ['code' => 'option22', 'labels' => ['en_US' => '[option22]']],
        ]);
    }

    public function test_it_returns_nothing(): void
    {
        $query = $this->getQuery();

        Assert::assertSame($query->search('unknown'), []);

        Assert::assertSame($query->search('attribute3'), []);
    }

    public function test_it_searches_on_code(): void
    {
        $query = $this->getQuery();

        Assert::assertSame($query->search(attributeCode: 'attribute1', search: '10'), [
            ['code' => 'option10', 'labels' => ['en_US' => '[option10]']],
        ]);
    }

    public function test_it_searches_on_labels(): void
    {
        $query = $this->getQuery();

        Assert::assertSame($query->search(attributeCode: 'attribute1', search: 'Option '), [
            ['code' => 'option1', 'labels' => ['en_US' => 'Option one']],
            ['code' => 'option3', 'labels' => ['en_US' => 'Option three']],
        ]);
    }

    public function test_it_returns_by_codes(): void
    {
        $query = $this->getQuery();

        Assert::assertSame($query->search('attribute1', includeCodes: ['option7', 'unknown']), [
            ['code' => 'option7', 'labels' => ['en_US' => '[option7]']],
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $attributeGroupId = $this->insertAttributeGroup();
        $attribute1Id = $this->insertAttribute($attributeGroupId, 'attribute1');
        $this->insertOptions($attribute1Id, [
            ['code' => 'option1', 'labels' => ['en_US' => 'Option one', 'fr_FR' => 'Option un'], 'sort_order' => 1],
            ['code' => 'option2', 'labels' => ['fr_FR' => 'Option two'], 'sort_order' => 2],
            ['code' => 'option3', 'labels' => ['en_US' => 'Option three'], 'sort_order' => 3],
            ['code' => 'option10', 'labels' => [], 'sort_order' => 10],
            ['code' => 'option11', 'labels' => [], 'sort_order' => 11],
            ['code' => 'option4', 'labels' => ['en_US' => 'Foobar'], 'sort_order' => 4],
            ['code' => 'option5', 'labels' => [], 'sort_order' => 5],
            ['code' => 'option6', 'labels' => [], 'sort_order' => 6],
            ['code' => 'option7', 'labels' => [], 'sort_order' => 7],
            ['code' => 'option8', 'labels' => [], 'sort_order' => 8],
            ['code' => 'option9', 'labels' => [], 'sort_order' => 9],
            ['code' => 'option12', 'labels' => [], 'sort_order' => 12],
            ['code' => 'option13', 'labels' => [], 'sort_order' => 13],
            ['code' => 'option14', 'labels' => [], 'sort_order' => 14],
            ['code' => 'option15', 'labels' => [], 'sort_order' => 15],
            ['code' => 'option16', 'labels' => [], 'sort_order' => 16],
            ['code' => 'option17', 'labels' => [], 'sort_order' => 17],
            ['code' => 'option18', 'labels' => [], 'sort_order' => 18],
            ['code' => 'option19', 'labels' => [], 'sort_order' => 19],
            ['code' => 'option20', 'labels' => [], 'sort_order' => 20],
            ['code' => 'option21', 'labels' => [], 'sort_order' => 21],
            ['code' => 'option22', 'labels' => [], 'sort_order' => 22],
        ]);


        $attribute2Id = $this->insertAttribute($attributeGroupId, 'attribute2');
        $this->insertOptions($attribute2Id, [
            ['code' => 'another_option', 'labels' => [], 'sort_order' => 1],
        ]);

        $this->insertAttribute($attributeGroupId, 'attribute3');
    }

    private function insertAttributeGroup(): int
    {
        $createDummyAttributeGroupSql = <<<SQL
            INSERT INTO pim_catalog_attribute_group (code, sort_order, created, updated) 
            VALUES ('dummy_group', 1, NOW(), NOW()) 
        SQL;
        $this->getConnection()->executeQuery($createDummyAttributeGroupSql);

        return $this->getConnection()->lastInsertId();
    }

    private function insertAttribute(int $attributeGroupId, string $code): int
    {
        $sqlAttribute = <<<SQL
            INSERT INTO pim_catalog_attribute (group_id, sort_order, is_required, is_unique, is_localizable, is_scopable, code, entity_type, attribute_type, backend_type, created, updated)
            VALUES (:group_id, 1, 0, 0, 0, 0, :code, 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', 'pim_catalog_simpleselect', 'option', NOW(), NOW())
SQL;
        $this->getConnection()->executeQuery($sqlAttribute, [
            'group_id' => $attributeGroupId,
            'code' => $code
        ]);

        return $this->getConnection()->lastInsertId();
    }

    private function insertOptions(int $attributeId, array $options): void
    {
        foreach ($options as $option) {
            $this->insertOption($attributeId, $option['code'], $option['labels'], $option['sort_order']);
        }
    }

    private function insertOption(int $attributeId, string $code, array $labels, int $sortOrder): void
    {
        $sqlOption = <<<SQL
            INSERT INTO pim_catalog_attribute_option (attribute_id, code, sort_order)
            VALUES (:attribute_id, :code, :sort_order)
SQL;
        $this->getConnection()->executeQuery($sqlOption, [
            'attribute_id' => $attributeId,
            'code' => $code,
            'sort_order' => $sortOrder,
        ]);

        $optionId = $this->getConnection()->lastInsertId();
        foreach ($labels as $locale => $value) {
            $this->insertTranslation($optionId, $locale, $value);
        }
    }

    private function insertTranslation(int $optionId, string $locale, string $value)
    {
        $sqlTranslation = <<<SQL
            INSERT INTO pim_catalog_attribute_option_value (option_id, locale_code, value)
            VALUES (:option_id, :locale_code, :value)
SQL;
        $this->getConnection()->executeQuery($sqlTranslation, [
            'option_id' => $optionId,
            'locale_code' => $locale,
            'value' => $value,
        ]);
    }

    private function getQuery(): SqlFindAttributeOptions
    {
        return $this->get('Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeOption\FindAttributeOptions');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
