<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetCategoryCodesByProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\CategoryTree\CategoryTreeFixturesLoader;
use Webmozart\Assert\Assert;

class GetCategoryCodesByProductModelCodesIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;

    /** @var CategoryTreeFixturesLoader */
    private $fixturesLoader;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixturesLoader = $this->get('akeneo_integration_tests.loader.category_tree_loader');
        $this->entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $this->givenTheFollowingCategoryTrees([
            'root_master' => [
                'men' => [
                    'men_accessories' => [
                        'men_watch' => []
                    ],
                    'men_famous' => [],
                ],
                'women' => [
                    'women_accessories' => [
                        'women_watch' => []
                    ],
                    'women_famous' => [],
                ],
                'trending' => [],
                'top_sells_2018' => [],
                'shop_2019' => [],
                'season_2019' => [
                    'winter' => [],
                    'summer' => [],
                    'spring' => [],
                    'autumn' => []
                ]
            ],
        ]);

        $rootProductModels = $this->givenTheFollowingRootProductModelsWithCategories([
            'root_product_model_1' => ['men', 'trending', 'winter'],
            'root_product_model_2' => [],
            'root_product_model_3' => ['women', 'top_sells_2018']
        ]);

        $this->givenTheFollowingSubProductModelWithCategories($rootProductModels['root_product_model_1'], [
            'sub_product_model_1_1' => [],
            'sub_product_model_1_2' => ['shop_2019', 'women_famous']
        ]);

        $this->givenTheFollowingSubProductModelWithCategories($rootProductModels['root_product_model_2'], [
           'sub_product_model_2_1' => ['women_watch', 'summer'],
           'sub_product_model_2_2' => []
        ]);
    }

    public function testGetCategoryCodesForRootProductModels(): void
    {
        $expected = ['root_product_model_1' => ['men', 'trending', 'winter']];
        $actual = $this->getQuery()->fromProductModelCodes(['root_product_model_1']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesForRootProductModelsWithoutCategories(): void
    {
        $expected = ['root_product_model_2' => []];
        $actual = $this->getQuery()->fromProductModelCodes(['root_product_model_2']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesForMultipleRootProductModels(): void
    {
        $expected = ['root_product_model_2' => [], 'root_product_model_3' => ['women', 'top_sells_2018'], 'root_product_model_1' => ['men', 'trending', 'winter']];
        $actual = $this->getQuery()->fromProductModelCodes(['root_product_model_1', 'root_product_model_2', 'root_product_model_3']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesForMultipleSubProductModels(): void
    {
        $expected = [
            'sub_product_model_1_1' => ['men', 'trending', 'winter'],
            'sub_product_model_1_2' => ['men', 'trending', 'winter', 'shop_2019', 'women_famous'],
            'sub_product_model_2_1' => ['women_watch', 'summer'],
            'sub_product_model_2_2' => []
        ];

        $actual = $this->getQuery()->fromProductModelCodes(['sub_product_model_1_1', 'sub_product_model_1_2', 'sub_product_model_2_1', 'sub_product_model_2_2']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesOnProductModelsOfDifferentLevels(): void
    {
        $expected = [
            'root_product_model_1' => ['men', 'trending', 'winter'],
            'root_product_model_2' => [],
            'sub_product_model_1_1' => ['men', 'trending', 'winter'],
            'sub_product_model_2_2' => [],
        ];

        $actual = $this->getQuery()->fromProductModelCodes(['root_product_model_1', 'root_product_model_2', 'sub_product_model_1_1', 'sub_product_model_2_2']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getQuery(): GetCategoryCodesByProductModelCodes
    {
        return $this->get('akeneo.pim.enrichment.category.query.category_codes_by_product_model_codes');
    }

    private function givenTheFollowingCategoryTrees(array $categoryTrees): void
    {
        $this->fixturesLoader->givenTheCategoryTrees($categoryTrees);

        $this->givenBooleanAttributes(['first_yes_no', 'second_yes_no']);
        $this->givenFamilies([['code' => 'aFamily', 'attribute_codes' => ['first_yes_no', 'second_yes_no']]]);

        $this->entityBuilder->createFamilyVariant(
            [
                'code' => 'familyVariantWithTwoLevels',
                'family' => 'aFamily',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['first_yes_no'],
                        'attributes' => [],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['second_yes_no'],
                        'attributes' => [],
                    ],
                ],
            ]
        );
    }

    private function givenTheFollowingRootProductModelsWithCategories(array $rootProductModelCodes): array
    {
        $results = [];

        foreach ($rootProductModelCodes as $rootProductModelCode => $categories) {
            $productModel = $this->entityBuilder->createProductModel($rootProductModelCode, 'familyVariantWithTwoLevels', null, ['categories' => $categories]);
            $results[$rootProductModelCode] = $productModel;
        }

        return $results;
    }

    private function givenTheFollowingSubProductModelWithCategories(ProductModelInterface $rootProductModel, array $subProductModelCodes)
    {
        foreach ($subProductModelCodes as $subProductModelCode => $categories) {
            $this->entityBuilder->createProductModel($subProductModelCode, 'familyVariantWithTwoLevels', $rootProductModel, ['categories' => $categories]);
        }
    }

    private function givenBooleanAttributes(array $codes): void
    {
        $attributes = array_map(function (string $code) {
            $data = [
                'code' => $code,
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other'
            ];

            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
            $constraints = $this->get('validator')->validate($attribute);
            Assert::count($constraints, 0);

            return $attribute;
        }, $codes);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function givenFamilies(array $familiesData): void
    {
        $families = array_map(function ($data) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, [
                'code' => $data['code'],
                'attributes'  => array_merge(['sku'], $data['attribute_codes']),
                'attribute_requirements' => ['ecommerce' => ['sku']]
            ]);

            $errors = $this->get('validator')->validate($family);
            Assert::count($errors, 0);

            return $family;
        }, $familiesData);


        $this->get('pim_catalog.saver.family')->saveAll($families);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
