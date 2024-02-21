<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductModelAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelAssociationsByProductUuidsIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;

    public function setUp(): void
    {
        parent::setUp();

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

        $this->givenTheFollowingAssociationFromProductsToProductModels([
            'productA' => [],
            'productB' => ['UPSELL' => ['productModelA']],
            'productC' => ['X_SELL' => ['productModelA', 'productModelB'], 'PACK' => ['productModelC']]
        ]);

        $rootProductModels = $this->givenTheFollowingAssociationFromRootProductModelsToProductModels([
            'root_product_model' => ['X_SELL' => ['productModelF'], 'PACK' => ['productModelA', 'productModelC']],
        ]);

        $subProductModels = $this->givenTheFollowingAssociationFromSubProductModelsToProductModels($rootProductModels['root_product_model'], [
            'sub_product_model' => ['X_SELL' => ['productModelD'], 'SUBSTITUTION' => ['productModelB']]
        ]);

        $this->givenTheFollowingAssociationFromVariantProductToProductModels($subProductModels['sub_product_model'], [
            'variant_product_1' => ['PACK' => ['productModelG'], 'UPSELL' => ['productModelE'],'X_SELL' => ['productModelF']]
        ]);

        $this->givenAssociationTypes(['A_NEW_TYPE']);
    }

    public function testWithAProductContainingNoAssociation()
    {
        $uuidProductA = $this->getProductUuid('productA');
        $expected = [$uuidProductA->toString() => $this->getAssociationsFormattedAfterFetch()];
        $actual = $this->getQuery()->fetchByProductUuids([$uuidProductA]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnASingleProduct()
    {
        $uuidProductC = $this->getProductUuid('productC');
        $expected = [
            $uuidProductC->toString() => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC'])
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$uuidProductC]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleSimpleProduct()
    {
        $uuidProductA = $this->getProductUuid('productA');
        $uuidProductB = $this->getProductUuid('productB');
        $uuidProductC = $this->getProductUuid('productC');
        $expected = [
            $uuidProductA->toString() => $this->getAssociationsFormattedAfterFetch(),
            $uuidProductB->toString() => $this->getAssociationsFormattedAfterFetch([], [], [], ['productModelA']),
            $uuidProductC->toString() => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC']),
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$uuidProductA, $uuidProductB, $uuidProductC]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleWithProductModels()
    {
        $uuidProductA = $this->getProductUuid('productA');
        $uuidProductB = $this->getProductUuid('productB');
        $uuidProductC = $this->getProductUuid('productC');
        $uuidVariantProduct1 = $this->getProductUuid('variant_product_1');
        $expected = [
            $uuidProductA->toString() => $this->getAssociationsFormattedAfterFetch(),
            $uuidProductC->toString() => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC']),
            $uuidProductB->toString() => $this->getAssociationsFormattedAfterFetch([], [], [], ['productModelA']),
            $uuidVariantProduct1->toString() => $this->getAssociationsFormattedAfterFetch(['productModelF', 'productModelD'], ['productModelA', 'productModelC', 'productModelG'], ['productModelB'], ['productModelE'])
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$uuidProductA, $uuidProductC, $uuidProductB, $uuidVariantProduct1]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function givenTheFollowingAssociationFromProductsToProductModels(array $productsCodeToAssociations) {
        foreach ($productsCodeToAssociations as $productCode => $associationTypesToProductModelCode) {
            $associations = [];
            foreach ($associationTypesToProductModelCode as $associationType => $productModelCodes) {
                $associations[$associationType] = ['product_models' => $productModelCodes];
            }
            $this->entityBuilder->createProduct($productCode, 'aFamily', ['associations' => $associations]);
        }
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

    private function givenTheFollowingAssociationFromVariantProductToProductModels(ProductModelInterface $productModel, array $variantProductsCodeToAssociations): void
    {
        foreach ($variantProductsCodeToAssociations as $variantProductCode => $associationTypesToProductModelCode) {
            $associations = [];
            foreach ($associationTypesToProductModelCode as $associationType => $productModelCodes) {
                $associations[$associationType] = ['product_models' => $productModelCodes];
            }
            $this->entityBuilder->createVariantProduct($variantProductCode, 'aFamily', 'familyVariantWithTwoLevels', $productModel,  ['associations' => $associations]);
        }
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
        return $this->get('Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductModelAssociationsByProductUuids');
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
