<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Storage\Save\Query\UpsertCategoryBaseSql;

class UpsertCategoryBaseSqlIntegration extends CategoryTestCase
{
    public function testInsertNewCategoryInDatabase(): void
    {
        /** @var UpsertCategoryBaseSql $upsertCategoryBaseQuery */
        $upsertCategoryBaseQuery = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(UpsertCategoryBaseSql::class, $upsertCategoryBaseQuery::class);

        $categoryCode = 'myCategory';
        $baseCompositeKey = 'seo_meta_description' . AbstractValue::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d';
        $expectedCompositeKey = $baseCompositeKey . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US';

        $expectedData = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ]);

        $category = new Category(
            id: null,
            code: new Code($categoryCode),
            templateUuid: null,
            attributes: $expectedData,
        );
        $upsertCategoryBaseQuery->execute($category);

        $categoryInserted = $this->getCategoryByCode('myCategory');
        $valueCollectionInserted = \json_decode($categoryInserted['value_collection'], true);
        $valueInserted = $valueCollectionInserted[$expectedCompositeKey];

        $this->assertSame((string)$category->getCode(), $categoryInserted['code']);
        $this->assertArrayHasKey('attribute_codes', $valueCollectionInserted);
        $this->assertEquals(
            $category->getAttributeCodes(),
            $valueCollectionInserted['attribute_codes']
        );

        $this->assertArrayHasKey($expectedCompositeKey, $valueCollectionInserted);

        $this->assertEquals(
            /** @phpstan-ignore-next-line */
            $expectedData->getValues()[0]->getValue(),
            $valueInserted['data']
        );
    }

    public function testUpdateExistingCategoryInDatabase(): void
    {
        $categoryCode = new Code('myCategory');
        $categoryInserted = $this->insertBaseCategory($categoryCode);

        $baseCompositeKey = 'seo_meta_description' . AbstractValue::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d';
        $expectedCompositeKey = $baseCompositeKey . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US';

        $expectedParentId = new CategoryId($categoryInserted->getId()->getValue());

        // Update Category
        $expectedData = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ]);
        $categoryToUpdate = new Category(
            id: $categoryInserted->getId(),
            code: $categoryInserted->getCode(),
            templateUuid: null,
            parentId: $expectedParentId,
            attributes: $expectedData
        );

        /** @var UpsertCategoryBaseSql $upsertCategoryBaseSql */
        $upsertCategoryBaseSql = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(UpsertCategoryBaseSql::class, $upsertCategoryBaseSql::class);

        // Update Category previously inserted
        $upsertCategoryBaseSql->execute($categoryToUpdate);

        $updatedCategory = $this->getCategoryByCode('myCategory');
        $valueCollectionUpdated = \json_decode($updatedCategory['value_collection'], true);
        $valueUpdated = $valueCollectionUpdated[$expectedCompositeKey];

        $this->assertSame($updatedCategory['code'], (string)$categoryInserted->getCode());
        $this->assertEquals($updatedCategory['parent_id'], $categoryInserted->getParentId()?->getValue());
        $this->assertEquals($updatedCategory['root_id'], $categoryInserted->getRootId()?->getValue());

        $this->assertArrayHasKey('attribute_codes', $valueCollectionUpdated);
        $this->assertNotEquals(
            $valueCollectionUpdated['attribute_codes'],
            $categoryInserted->getAttributeCodes()
        );

        $this->assertArrayHasKey($expectedCompositeKey, $valueCollectionUpdated);

        $this->assertEquals(
            /** @phpstan-ignore-next-line */
            $expectedData->getValues()[0]->getValue(),
            $valueUpdated['data']
        );
    }

    public function testUpdateCategoryValueCollectionWithNullValue(): void
    {
        // Given
        $categoryCode = new Code('myCategory');
        $categoryInserted = $this->insertBaseCategory($categoryCode);

        // When
        $valueCollection = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
            TextValue::fromApplier(
                value: null,
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'fr_FR'
            )
        ]);
        $categoryToUpdate = new Category(
            id: $categoryInserted->getId(),
            code: $categoryInserted->getCode(),
            templateUuid: null,
            parentId: null,
            attributes: $valueCollection,
        );

        /** @var UpsertCategoryBaseSql $upsertCategoryBaseSql */
        $upsertCategoryBaseSql = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(UpsertCategoryBaseSql::class, $upsertCategoryBaseSql::class);

        $upsertCategoryBaseSql->execute($categoryToUpdate);

        // Then
        $updatedCategory = $this->getCategoryByCode('myCategory');
        $valueCollectionUpdated = \json_decode($updatedCategory['value_collection'], true);

        $this->assertEquals(
            [
                "attribute_codes" => [
                    "seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d"
                ],
                "seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|ecommerce|en_US" => [
                    "data" => "Meta shoes",
                    "type" => "text",
                    "locale" => "en_US",
                    "channel" => "ecommerce",
                    "attribute_code" => "seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d",
                ],
            ],
            $valueCollectionUpdated
        );
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function getCategoryByCode(string $categoryCode): array
    {
        $sqlQuery = <<<SQL
            SELECT
                category.id,
                category.code,
                category.parent_id,
                category.root as root_id,
                category.value_collection
            FROM
                pim_catalog_category category
            WHERE category.code = :category_code
        SQL;

        return $this->get('database_connection')->executeQuery(
            $sqlQuery,
            ['category_code' => $categoryCode],
            ['category_code' => \PDO::PARAM_STR]
        )->fetchAssociative();
    }
}
