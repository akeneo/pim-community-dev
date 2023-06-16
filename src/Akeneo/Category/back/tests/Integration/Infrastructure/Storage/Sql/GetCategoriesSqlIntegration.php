<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as CategoryDoctrine;
use Akeneo\Category\ServiceApi\ExternalApiCategory;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesSqlIntegration extends CategoryTestCase
{
    private CategoryDoctrine|Category $categoryShoes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $this->categoryShoes = $this->createCategory([
            'code' => 'shoes',
            'labels' => [
                'fr_FR' => 'Chaussures',
                'en_US' => 'Shoes'
            ]
        ]);

        $this->createCategory([
            'code' => 'boots',
            'labels' => [
                'fr_FR' => 'Bottes',
                'en_US' => 'Boots'
            ],
            'parent' => $this->categoryShoes->getCode(),
        ]);

        $this->createCategory([
            'code' => 'sandals',
            'labels' => [
                'fr_FR' => 'Sandales',
                'en_US' => 'Sandals'
            ],
            'parent' => $this->categoryShoes->getCode(),
        ]);

        $this->createCategory([
            'code' => 'slippers',
            'labels' => [
                'fr_FR' => 'Pantoufles',
                'en_US' => 'Slippers'
            ],
            'parent' => $this->categoryShoes->getCode(),
        ]);

        $this->createCategory([
            'code' => 'pants',
            'labels' => [
                'fr_FR' => 'Pantalons',
                'en_US' => 'Pants'
            ]
        ]);
    }

    public function testReturnExternalCategoryApiList(): void
    {
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:category_codes)',
            params: [
                'category_codes' => ['shoes'],
                'with_enriched_attributes' => false,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategories);
        $this->assertContainsOnlyInstancesOf(ExternalApiCategory::class, $retrievedCategories);
    }

    public function testDoNotGetCategoryByCodes(): void
    {
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:category_codes)',
            params: [
                'category_codes' => ['wrong code'],
                'with_enriched_attributes' => true,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );
        $category = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($category);
        $this->assertEmpty($category);
    }

    public function testGetCategoryByCodes(): void
    {
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:category_codes)',
            params: [
                'category_codes' => ['socks', 'shoes'],
                'with_enriched_attributes' => false,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategories);
        // we retrieve 2 out of the 3 existing categories
        $this->assertCount(2, $retrievedCategories);

        $shoesCategory = null;
        $socksCategory = null;

        foreach ($retrievedCategories as $category) {
            if ((string) $category->getCode() === 'shoes') {
                $this->assertEmpty($shoesCategory);
                $shoesCategory = $category;
            }
            if ((string) $category->getCode() === 'socks') {
                $this->assertEmpty($socksCategory);
                $socksCategory = $category;
            }
        }

        $this->assertNotEmpty($shoesCategory);
        $this->assertNotEmpty($socksCategory);

        // we check labels of retrieved categories
        $this->assertEqualsCanonicalizing(
            [
                'fr_FR' => 'Chaussures',
                'en_US' => 'Shoes'
            ],
            $shoesCategory->getLabels()
        );
        $this->assertEqualsCanonicalizing(
            [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ],
            $socksCategory->getLabels()
        );

        $this->assertNull($socksCategory->getValues());
    }

    public function testGetCategoryByCodesWithPosition(): void
    {
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:category_codes)',
            params: [
                'category_codes' => ['sandals', 'slippers', 'pants'],
                'with_enriched_attributes' => true,
                'with_position' => true,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertCount(3, $retrievedCategories);

        /**
         * Categories are organized like this in DB:
         *
         * category_code        : position
         * master               : 1
         * shoes                : 1
         *  |_ boots            : 1
         *  |_ sandals          : 2
         *  |_ slippers         : 3
         * socks                : 1
         * pants                : 1
         */
        foreach ($retrievedCategories as $category) {
            switch ($category->getCode()) {
                case 'sandals':
                    $this->assertPositionIs(2, $category);
                    break;
                case 'slippers':
                    $this->assertPositionIs(3, $category);
                    break;
                case 'pants':
                    $this->assertPositionIs(1, $category);
                    break;
                default:
                    break;
            }
        }
    }

    public function testGetCategoryByCodesWithEnrichedAttributes(): void
    {
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $this->categoryShoes->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $this->updateCategoryWithValues($this->categoryShoes->getCode());

        $parameters = new ExternalApiSqlParameters(
            sqlWhere: '1=1',
            params: [
                'category_codes' => [],
                'with_enriched_attributes' => true,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategories);

        /** @var ExternalApiCategory $category */
        foreach ($retrievedCategories as $category) {
            switch ($category->getCode()) {
                case 'shoes':
                    $this->assertIsArray($category->getValues());
                    $this->assertArrayHasKey('photo|8587cda6-58c8-47fa-9278-033e1d8c735c', $category->getValues());
                    $this->assertArrayHasKey('title|87939c45-1d85-4134-9579-d594fff65030|ecommerce|en_US', $category->getValues());
                    $this->assertArrayHasKey('title|87939c45-1d85-4134-9579-d594fff65030|ecommerce|fr_FR', $category->getValues());
                    break;
                case 'boots':
                case 'socks':
                case 'pants':
                case 'sandals':
                case 'slippers':
                    $this->assertNull($category->getValues());
                    break;
                default:
                    break;
            }
        }
    }

    public function testGetAllCategoriesWithTheirValuesWhenTemplateActivatedAndEnrichedCategories(): void
    {
        // Given an enriched parent category and attached a template.
        $parentCategory = $this->useTemplateFunctionalCatalog(
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            categoryCode: 'myParentCategory'
        );
        $this->updateCategoryWithValues((string) $parentCategory->getCode());

        // Given an enriched child category
        $childCategory = $this->createOrUpdateCategory(
            code: "myChildCategory",
            labels: [
                'fr_FR' => 'Ma categorie enfant',
                'en_US' => 'My child category'
            ],
            parentId: $parentCategory->getId()?->getValue(),
            rootId: $parentCategory->getId()?->getValue(),
        );
        $this->updateCategoryWithValues((string) $childCategory->getCode());

        // When
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:category_codes)',
            params: [
                'category_codes' => ['myParentCategory', 'myChildCategory'],
                'with_enriched_attributes' => true,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );

        /** @var ExternalApiCategory[] $retrievedCategories */
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);

        // Then
        $this->assertIsArray($retrievedCategories);
        $this->assertCount(2, $retrievedCategories);
        $this->assertContainsOnlyInstancesOf(ExternalApiCategory::class, $retrievedCategories);
        $this->assertNotEmpty($retrievedCategories[0]->getValues());
        $this->assertNotEmpty($retrievedCategories[1]->getValues());
    }

    public function testGetCategoriesWithTheirValuesIfAnyWhenTemplateActivatedAndEnrichedCategories(): void
    {
        // Given a non enriched parent category and attached a template.
        $parentCategory = $this->useTemplateFunctionalCatalog(
            templateUuid: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            categoryCode: 'myParentCategory'
        );

        // Given an enriched child category
        $childCategory = $this->createOrUpdateCategory(
            code: "myChildCategory",
            labels: [
                'fr_FR' => 'Ma categorie enfant',
                'en_US' => 'My child category'
            ],
            parentId: $parentCategory->getId()?->getValue(),
            rootId: $parentCategory->getId()?->getValue(),
        );
        $this->updateCategoryWithValues((string) $childCategory->getCode());

        // Given a non enriched child category
        $this->createOrUpdateCategory(
            code: "mySecondChildCategory",
            labels: [
                'fr_FR' => 'Ma seconde categorie enfant',
                'en_US' => 'My second child category'
            ],
            parentId: $parentCategory->getId()?->getValue(),
            rootId: $parentCategory->getId()?->getValue(),
        );

        // When
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:category_codes)',
            params: [
                'category_codes' => ['myParentCategory', 'myChildCategory', 'mySecondChildCategory'],
                'with_enriched_attributes' => true,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );

        /** @var ExternalApiCategory[] $retrievedCategories */
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);

        // Then
        $this->assertIsArray($retrievedCategories);
        $this->assertCount(3, $retrievedCategories);
        $this->assertContainsOnlyInstancesOf(ExternalApiCategory::class, $retrievedCategories);

        $this->assertSame('myChildCategory', $retrievedCategories[0]->getCode());
        $this->assertNotEmpty($retrievedCategories[0]->getValues());

        $this->assertSame('myParentCategory', $retrievedCategories[1]->getCode());
        $this->assertNull($retrievedCategories[1]->getValues());

        $this->assertSame('mySecondChildCategory', $retrievedCategories[2]->getCode());
        $this->assertNull($retrievedCategories[2]->getValues());
    }

    public function testGetCategoryByCodesWithIgnoredEnrichedAttributes(): void
    {
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $this->categoryShoes->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $this->updateCategoryWithValues($this->categoryShoes->getCode());

        $this->deactivateTemplate($templateModel->getUuid()->getValue());

        $parameters = new ExternalApiSqlParameters(
            sqlWhere: '1=1',
            params: [
                'category_codes' => [],
                'with_enriched_attributes' => true,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategories);

        /** @var ExternalApiCategory $category */
        foreach ($retrievedCategories as $category) {
            switch ($category->getCode()) {
                case 'shoes':
                case 'boots':
                case 'socks':
                case 'pants':
                case 'sandals':
                case 'slippers':
                    $this->assertNull($category->getValues());
                    break;
                default:
                    break;
            }
        }
    }

    public function testGetCategoryByCodesWithoutEnrichedCategories(): void
    {
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:category_codes)',
            params: [
                'category_codes' => ['shoes'],
                'with_enriched_attributes' => false,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 10',
        );
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategories);
        // we retrieve 1 out of the 3 existing categories
        $this->assertCount(1, $retrievedCategories);

        $shoesCategory = null;

        foreach ($retrievedCategories as $category) {
            $this->assertEmpty($shoesCategory);
            $shoesCategory = $category;
        }

        $this->assertNotEmpty($shoesCategory);

        // we check labels of retrieved categories
        $this->assertEqualsCanonicalizing(
            [
                'fr_FR' => 'Chaussures',
                'en_US' => 'Shoes'
            ],
            $shoesCategory->getLabels()
        );

        $this->assertNull($shoesCategory->getValues());
    }

    public function testGetCategoryWithLimitSetToOneAndOffsetToTwo(): void
    {
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: '1=1',
            params: [
                'with_enriched_attributes' => false,
                'with_position' => false,
            ],
            types: [
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
            limitAndOffset: 'LIMIT 1 OFFSET 2',
        );
        $retrievedCategory = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategory);

        // we retrieve only 1 out of the 3 existing categories
        $this->assertCount(1, $retrievedCategory);

        // we check that we retrieved the correct category according to the OFFSET
        $this->assertSame('shoes', (string) $retrievedCategory[0]->getCode());
    }

    public function testDoesNotGetCategoryWithLabelsNullOrEmpty(): void
    {
        $this->createOrUpdateCategory(
            code: "with_labels_null",
            labels: [
                'fr_FR' => null,
                'en_US' => null,
            ],
        );

        $this->createOrUpdateCategory(
            code: "with_labels_empty",
            labels: [
                'fr_FR' => '',
                'en_US' => '',
            ],
        );
        $parameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:category_codes)',
            params: [
                'category_codes' => ['with_labels_null', 'with_labels_empty'],
                'with_enriched_attributes' => false,
                'with_position' => false,
            ],
            types: [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL,
                'with_position' => \PDO::PARAM_BOOL,
            ],
        );

        /** @var array<ExternalApiCategory> $retrievedCategory */
        $retrievedCategory = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategory);

        $this->assertCount(2, $retrievedCategory);
        $this->assertEmpty($retrievedCategory[0]->getLabels());
        $this->assertEmpty($retrievedCategory[1]->getLabels());
    }

    public function testCountCategories(): void
    {
        $parameters = new ExternalApiSqlParameters('1=1', [], []);
        $numberOfCategories = $this->get(GetCategoriesInterface::class)->count($parameters);

        $this->assertIsInt($numberOfCategories);
        $this->assertSame(7, $numberOfCategories);
    }

    public function testCountCategoriesByCodes(): void
    {
        $parameters = new ExternalApiSqlParameters(
            'category.code IN (:category_codes)',
            ['category_codes' => ['socks', 'shoes']],
            ['category_codes' => Connection::PARAM_STR_ARRAY]
        );
        $numberOfCategories = $this->get(GetCategoriesInterface::class)->count($parameters);

        $this->assertIsInt($numberOfCategories);
        $this->assertSame(2, $numberOfCategories);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertPositionIs(int $expectedPosition, ExternalApiCategory $category): void
    {
        $this->assertEquals($expectedPosition, $category->getPosition(), sprintf('Position of %s', $category->getCode()));
    }
}
