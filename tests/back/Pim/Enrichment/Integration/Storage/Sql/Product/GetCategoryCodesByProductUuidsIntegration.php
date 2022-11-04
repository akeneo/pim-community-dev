<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductUuids;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Webmozart\Assert\Assert;

class GetCategoryCodesByProductUuidsIntegration extends TestCase
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
        $uuidProductA = $this->getProductUuid('productA');
        $expected = [$uuidProductA->toString() => ['men', 'shop_2019']];
        $actual = $this->getQuery()->fetchCategoryCodes([$uuidProductA]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesForAVariantProduct(): void
    {
        $uuidVariantProduct1 = $this->getProductUuid('variant_product_1');
        $expected = [$uuidVariantProduct1->toString() => ['trending', 'watch', 'famous', 'men']];
        $actual = $this->getQuery()->fetchCategoryCodes([$uuidVariantProduct1]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesOnMultipleIdentifiers(): void
    {
        $uuidVariantProduct1 = $this->getProductUuid('variant_product_1');
        $uuidProductA = $this->getProductUuid('productA');
        $uuidVariantProduct2 = $this->getProductUuid('variant_product_2');

        $expected = [
            $uuidVariantProduct1->toString() => ['trending', 'watch', 'famous', 'men'],
            $uuidProductA->toString() => ['men', 'shop_2019'],
            $uuidVariantProduct2->toString() => ['watch', 'famous', 'men'],
        ];
        $actual = $this->getQuery()->fetchCategoryCodes([
            $uuidProductA,
            $uuidVariantProduct1,
            $uuidVariantProduct2
        ]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesOnProductWithoutCategory(): void
    {
        $uuidProductB = $this->getProductUuid('productB');
        $expected = [$uuidProductB->toString() => []];
        $actual = $this->getQuery()->fetchCategoryCodes([$uuidProductB]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getQuery(): GetCategoryCodesByProductUuids
    {
        return $this->get('Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductUuids');
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
