<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductAssociationsByProductIdentifiers;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductAssociationsByProductIdentifiersIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $this->givenBooleanAttributes(['first_yes_no', 'second_yes_no']);
        $this->givenFamilies([['code' => 'aFamily', 'attribute_codes' => ['first_yes_no', 'second_yes_no']]]);
        $entityBuilder->createFamilyVariant(
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

        $entityBuilder->createProduct('productA', 'aFamily', []);
        $entityBuilder->createProduct('productB', 'aFamily', []);
        $entityBuilder->createProduct('productC', 'aFamily', $this->getAssociationsFormatted([], [], [], ['productA']));
        $entityBuilder->createProduct('productD', 'aFamily', $this->getAssociationsFormatted(['productA', 'productB'], ['productC']));
        $entityBuilder->createProduct('productE', 'aFamily', []);
        $entityBuilder->createProduct('productF', 'aFamily', []);
        $entityBuilder->createProduct('productG', 'aFamily', []);
        $rootProductModel = $entityBuilder->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, $this->getAssociationsFormatted(['productF'], ['productA', 'productC']));
        $subProductModel1 = $entityBuilder->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, $this->getAssociationsFormatted(['productD'], [], ['productB']));
        $entityBuilder->createVariantProduct('variant_product_1', 'aFamily', 'familyVariantWithTwoLevels', $subProductModel1, $this->getAssociationsFormatted([], ['productG'], [], ['productE']));

        $this->givenAssociationTypes(['A_NEW_TYPE']);
    }

    public function testWithAProductContainingNoAssociation()
    {
        $expected = ['productE' => $this->getAssociationsFormattedAfterFetch()];
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productE']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnASingleProduct()
    {
        $expected = [
            'productD' => $this->getAssociationsFormattedAfterFetch(['productA', 'productB'], ['productC'])
        ];
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productD']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleSimpleProduct()
    {
        $expected = [
            'productE' => $this->getAssociationsFormattedAfterFetch(),
            'productD' => $this->getAssociationsFormattedAfterFetch(['productA', 'productB'], ['productC']),
            'productC' => $this->getAssociationsFormattedAfterFetch([], [], [], ['productA']),
        ];
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productE', 'productC', 'productD']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleWithProductModels()
    {
        $expected = [
            'productE' => $this->getAssociationsFormattedAfterFetch(),
            'productD' => $this->getAssociationsFormattedAfterFetch(['productA', 'productB'], ['productC']),
            'productC' => $this->getAssociationsFormattedAfterFetch([], [], [], ['productA']),
            'variant_product_1' => $this->getAssociationsFormattedAfterFetch(['productF', 'productD'], ['productA', 'productC', 'productG'], ['productB'], ['productE']),
        ];
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productE', 'productC', 'productD', 'variant_product_1']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getQuery(): GetProductAssociationsByProductIdentifiers
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_associations_by_product_identifiers');
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

    private function givenBooleanAttributes(array $codes): void
    {
        $attributes = array_map(function (string $code) {
            $data = [
                'code' => $code,
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
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

    private function getAssociationsFormatted(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [], array $aNewType = [])
    {
        return ['associations' => [
            'X_SELL' => ['products' => $crossSell],
            'PACK' => ['products' => $pack],
            'SUBSTITUTION' => ['products' => $substitutions],
            'UPSELL' => ['products' => $upsell],
        ]];
    }

    private function getAssociationsFormattedAfterFetch(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [], array $aNewType = []): array
    {
        return [
            'X_SELL' => ['products' => $crossSell],
            'PACK' => ['products' => $pack],
            'SUBSTITUTION' => ['products' => $substitutions],
            'UPSELL' => ['products' => $upsell],
            'A_NEW_TYPE' => ['products' => $aNewType]
        ];
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
