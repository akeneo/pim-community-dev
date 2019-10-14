<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductIdentifiers;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\CategoryTree\CategoryTreeFixturesLoader;
use Webmozart\Assert\Assert;

class GetCategoryCodesByProductIdentifiersIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.category_tree_loader');
        $this->entityBuilder =  $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $fixturesLoader->givenTheCategoryTrees([
            'root_master' => [
                'men' => [
                    'accessories' => [
                        'watch' => []
                    ],
                    'famous' => [],
                ],
                'trending' => [],
                'shop_2019' => [],
            ]
        ]);


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

        $fixturesLoader->givenTheProductsWithCategories(['productA' => ['men', 'shop_2019']]);
        $fixturesLoader->givenTheProductsWithCategories(['productB' => []]);
        $rootProductModel = $this->entityBuilder->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, ['categories' => ['men']]);
        $subProductModel1 = $this->entityBuilder->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, ['categories' => ['watch', 'famous']]);
        $this->entityBuilder->createVariantProduct('variant_product_1', 'aFamily', 'familyVariantWithTwoLevels', $subProductModel1, ['categories' => ['trending']]);
        $this->entityBuilder->createVariantProduct('variant_product_2', 'aFamily', 'familyVariantWithTwoLevels', $subProductModel1, ['categories' => []]);
    }

    public function testGetCategoryCodesForASimpleProduct(): void
    {
        $expected = ['productA' => ['men', 'shop_2019']];
        $actual = $this->getQuery()->fetchCategoryCodes(['productA']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesForAVariantProduct(): void
    {
        $expected = ['variant_product_1' => ['trending', 'watch', 'famous', 'men']];
        $actual = $this->getQuery()->fetchCategoryCodes(['variant_product_1']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesOnMultipleIdentifiers(): void
    {
        $expected = [
            'variant_product_1' => ['trending', 'watch', 'famous', 'men'],
            'productA' => ['men', 'shop_2019'],
            'variant_product_2' => ['watch', 'famous', 'men']
        ];
        $actual = $this->getQuery()->fetchCategoryCodes(['productA', 'variant_product_1', 'variant_product_2']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesOnProductWithoutCategory(): void
    {
        $expected = ['productB' => []];
        $actual = $this->getQuery()->fetchCategoryCodes(['productB']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getQuery(): GetCategoryCodesByProductIdentifiers
    {
        return $this->get('akeneo.pim.enrichment.product.query.category_codes_by_product_identifiers');
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
