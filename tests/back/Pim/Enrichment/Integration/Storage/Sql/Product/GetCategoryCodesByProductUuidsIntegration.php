<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\CategoryTree\CategoryTreeFixturesLoader;
use Webmozart\Assert\Assert;

class GetCategoryCodesByProductUuidsIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;
    /** @var ProductInterface[] $productList */
    private array $productList;

    public function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->get('pim_enrich.product.message_bus');

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

        $this->productList['productA'] = $this->createProduct('productA', [new SetFamily('aFamily'), new SetCategories(['men', 'shop_2019'])]);
        $this->productList['productB'] = $this->createProduct('productB', [new SetFamily('aFamily')]);

        $rootProductModel = $this->entityBuilder->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, ['categories' => ['men']]);
        $this->entityBuilder->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, ['categories' => ['watch', 'famous']]);

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createUser('admin', ['ROLE_USER', 'ROLE_ADMINISTRATOR'], ['IT support'])->getId();

        $this->productList['variant_product_1'] = $this->createProduct(
            'variant_product_1',
            [
                new SetFamily('aFamily'),
                new ChangeParent('sub_product_model_1'),
                new SetBooleanValue('second_yes_no', null, null, false),
                new SetCategories(['trending']),
            ],
            $userId
        );
        $this->productList['variant_product_2'] = $this->createProduct(
            'variant_product_2',
            [
                new SetFamily('aFamily'),
                new ChangeParent('sub_product_model_1'),
                new SetBooleanValue('second_yes_no', null, null, false),
            ],
            $userId
        );
    }

    public function testGetCategoryCodesForASimpleProduct(): void
    {
        $expected = [$this->productList['productA']->getUuid()->toString() => ['men', 'shop_2019']];
        $actual = $this->getQuery()->fetchCategoryCodes([$this->productList['productA']->getUuid()]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesForAVariantProduct(): void
    {
        $expected = [$this->productList['variant_product_1']->getUuid()->toString() => ['trending', 'watch', 'famous', 'men']];
        $actual = $this->getQuery()->fetchCategoryCodes([$this->productList['variant_product_1']->getUuid()]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesOnMultipleUuids(): void
    {
        $expected = [
            $this->productList['variant_product_1']->getUuid()->toString() => ['trending', 'watch', 'famous', 'men'],
            $this->productList['productA']->getUuid()->toString() => ['men', 'shop_2019'],
            $this->productList['variant_product_2']->getUuid()->toString() => ['watch', 'famous', 'men']
        ];
        $actual = $this->getQuery()->fetchCategoryCodes([
            $this->productList['productA']->getUuid(),
            $this->productList['variant_product_1']->getUuid(),
            $this->productList['variant_product_2']->getUuid()
        ]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetCategoryCodesOnProductWithoutCategory(): void
    {
        $expected = [$this->productList['productB']->getUuid()->toString() => []];
        $actual = $this->getQuery()->fetchCategoryCodes([$this->productList['productB']->getUuid()]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getQuery(): GetCategoryCodesByProductUuids
    {
        return $this->get('akeneo.pim.enrichment.product.query.category_codes_by_product_uuids');
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
