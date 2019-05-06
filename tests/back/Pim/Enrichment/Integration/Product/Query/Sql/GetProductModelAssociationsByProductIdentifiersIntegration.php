<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetProductModelAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelAssociationsByProductIdentifiersIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $entityBuilder = new EntityBuilder($this->testKernel->getContainer());
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
            'variant_product_1' => ['PACK' => ['productModelG'], 'UPSELL' => ['productModelE']]
        ]);

        $this->givenAssociationTypes(['A_NEW_TYPE']);
    }

    public function testWithAProductContainingNoAssociation()
    {
        $expected = ['productA' => $this->getAssociationsFormattedAfterFetch()];
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productA']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnASingleProduct()
    {
        $expected = [
            'productC' => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC'])
        ];
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productC']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleSimpleProduct()
    {
        $expected = [
            'productA' => $this->getAssociationsFormattedAfterFetch(),
            'productB' => $this->getAssociationsFormattedAfterFetch([], [], [], ['productModelA']),
            'productC' => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC']),
        ];
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productA', 'productB', 'productC']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleWithProductModels()
    {
        $expected = [
            'productA' => $this->getAssociationsFormattedAfterFetch(),
            'productC' => $this->getAssociationsFormattedAfterFetch(['productModelA', 'productModelB'], ['productModelC']),
            'productB' => $this->getAssociationsFormattedAfterFetch([], [], [], ['productModelA']),
            'variant_product_1' => $this->getAssociationsFormattedAfterFetch(['productModelF', 'productModelD'], ['productModelA', 'productModelC', 'productModelG'], ['productModelB'], ['productModelE'])
        ];
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productA', 'productC', 'productB', 'variant_product_1']);

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

    private function getQuery(): GetProductModelAssociationsByProductIdentifiers
    {
        return $this->testKernel->getContainer()->get('akeneo.pim.enrichment.product.query.get_product_model_associations_by_product_identifiers');
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
