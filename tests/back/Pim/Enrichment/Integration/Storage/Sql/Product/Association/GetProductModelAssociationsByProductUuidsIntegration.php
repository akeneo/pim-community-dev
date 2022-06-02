<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductModelAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductModelAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelAssociationsByProductUuidsIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;

    /**
     * @var ProductInterface[]
     */
    private array $productList;

    public function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->get('pim_enrich.product.message_bus');

        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $this->entityBuilder = $entityBuilder;

        $this->givenTheFollowingProductModels(
            'productModelA',
            'productModelB',
            'productModelC',
            'productModelD',
            'productModelE',
            'productModelF',
            'productModelG'
        );

        $this->productList['productA'] = $this->createProduct('productA', [new SetFamily('aFamily')]);
        $this->productList['productB'] = $this->createProduct(
            'productB',
            [
                new SetFamily('aFamily'),
                new AssociateProductModels('UPSELL', ['productModelA']),
            ]
        );
        $this->productList['productC'] = $this->createProduct(
            'productC',
            [
                new SetFamily('aFamily'),
                new AssociateProductModels('X_SELL', ['productModelA', 'productModelB']),
                new AssociateProductModels('PACK', ['productModelC']),
            ]
        );

        $rootProductModels = $this->givenTheFollowingAssociationFromRootProductModelsToProductModels([
            'root_product_model' => ['X_SELL' => ['productModelF'], 'PACK' => ['productModelA', 'productModelC']],
        ]);

        $subProductModels = $this->givenTheFollowingAssociationFromSubProductModelsToProductModels($rootProductModels['root_product_model'], [
            'sub_product_model' => ['X_SELL' => ['productModelD'], 'SUBSTITUTION' => ['productModelB']]
        ]);

        $this->productList['variant_product_1'] = $this->createProduct(
            'variant_product_1',
            [
                new SetFamily('aFamily'),
                new ChangeParent('sub_product_model'),
                new SetBooleanValue('second_yes_no', null, null, false),
                new AssociateProductModels('PACK', ['productModelG']),
                new AssociateProductModels('UPSELL', ['productModelE']),
                new AssociateProductModels('X_SELL', ['productModelF']),
            ]
        );

        $this->givenAssociationTypes(['A_NEW_TYPE']);
    }

    public function testWithAProductContainingNoAssociation()
    {
        $expected = [$this->productList['productA']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch()];
        $actual = $this->getQuery()->fetchByProductUuids([$this->productList['productA']->getUuid()]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnASingleProduct()
    {
        $expected = [
            $this->productList['productC']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC'])
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$this->productList['productC']->getUuid()]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleSimpleProduct()
    {
        $expected = [
            $this->productList['productA']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch(),
            $this->productList['productB']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch([], [], [], ['productModelA']),
            $this->productList['productC']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC']),
        ];
        $actual = $this->getQuery()->fetchByProductUuids([
            $this->productList['productA']->getUuid(),
            $this->productList['productB']->getUuid(),
            $this->productList['productC']->getUuid()
        ]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleWithProductModels()
    {
        $expected = [
            $this->productList['productA']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch(),
            $this->productList['productB']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC']),
            $this->productList['productC']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch([], [], [], ['productModelA']),
            $this->productList['variant_product_1']->getUuid()->toString() => $this->getAssociationsFormattedAfterFetch(['productModelF', 'productModelD'], ['productModelA', 'productModelC', 'productModelG'], ['productModelB'], ['productModelE'])
        ];
        $actual = $this->getQuery()->fetchByProductUuids([
            $this->productList['productA']->getUuid(),
            $this->productList['productC']->getUuid(),
            $this->productList['productB']->getUuid(),
            $this->productList['variant_product_1']->getUuid()
        ]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function givenTheFollowingAssociationFromRootProductModelsToProductModels(array $rootProductModelCodesToAssociations): array
    {
        $results = [];
        foreach ($rootProductModelCodesToAssociations as $rootProductModelCode => $associationTypesToProductModelCode) {
            $associations = [];
            foreach ($associationTypesToProductModelCode as $associationType => $productModelCodes) {
                $associations[$associationType] = ['product_models' => $productModelCodes];
            }
            $productModel = $this->entityBuilder->createProductModel($rootProductModelCode, 'familyVariantWithTwoLevels', null, ['associations' => $associations]);
            $results[$rootProductModelCode] = $productModel;
        }

        return $results;
    }

    private function givenTheFollowingAssociationFromSubProductModelsToProductModels(ProductModelInterface $rootProductModel, array $subProductModelCodesToAssociations): array
    {
        $results = [];
        foreach ($subProductModelCodesToAssociations as $subProductModelCode => $associationTypesToProductModelCode) {
            $associations = [];
            foreach ($associationTypesToProductModelCode as $associationType => $productModelCodes) {
                $associations[$associationType] = ['product_models' => $productModelCodes];
            }
            $productModel = $this->entityBuilder->createProductModel($subProductModelCode, 'familyVariantWithTwoLevels', $rootProductModel, ['associations' => $associations]);
            $results[$subProductModelCode] = $productModel;
        }

        return $results;
    }

    private function givenTheFollowingProductModels(string  ...$productModelCodes): void
    {
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

        foreach ($productModelCodes as $productModelCode) {
            $this->entityBuilder->createProductModel($productModelCode, 'familyVariantWithTwoLevels', null, []);
        }
    }

    private function getQuery(): GetProductModelAssociationsByProductUuids
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_model_associations_by_product_uuids');
    }

    private function givenAssociationTypes(array $codes): void
    {
        $associationTypes = array_map(function (string $code) {
            $associationType = $this->get('pim_catalog.factory.association_type')->create();

            $this->get('pim_catalog.updater.association_type')->update($associationType, ['code' => $code]);

            $errors = $this->get('validator')->validate($associationType);

            Assert::count($errors, 0);

            return $associationType;
        }, $codes);

        $this->get('pim_catalog.saver.association_type')->saveAll($associationTypes);
    }

    private function getAssociationsFormattedAfterFetch(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [], array $aNewType = []): array
    {
        return [
            'X_SELL' => ['product_models' => $crossSell],
            'PACK' => ['product_models' => $pack],
            'SUBSTITUTION' => ['product_models' => $substitutions],
            'UPSELL' => ['product_models' => $upsell],
            'A_NEW_TYPE' => ['product_models' => $aNewType]
        ];
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

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
