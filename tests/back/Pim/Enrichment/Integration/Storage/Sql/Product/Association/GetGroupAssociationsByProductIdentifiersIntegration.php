<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetGroupAssociationsByProductIdentifiers;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetGroupAssociationsByProductIdentifiersIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $this->givenGroup(['groupA', 'groupB', 'groupC', 'groupD', 'groupE', 'groupF', 'groupG']);

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
        $entityBuilder->createProduct('productB', 'aFamily', $this->getAssociationsFormatted([], [], [], ['groupA']));
        $entityBuilder->createProduct('productC', 'aFamily', $this->getAssociationsFormatted(['groupA', 'groupB'], ['groupC']));
        $rootProductModel = $entityBuilder->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, $this->getAssociationsFormatted(['groupF'], ['groupA', 'groupC']));
        $subProductModel1 = $entityBuilder->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, $this->getAssociationsFormatted(['groupD'], [], ['groupB']));
        $entityBuilder->createVariantProduct('variant_product_1', 'aFamily', 'familyVariantWithTwoLevels', $subProductModel1, $this->getAssociationsFormatted([], ['groupG'], [], ['groupE']));

        $this->givenAssociationTypes(['A_NEW_TYPE']);
    }

    public function testWithAProductContainingNoAssociation()
    {
        $expected = ['productA' => $this->getAssociationsFormattedAfterFetch()];
        $actual = $this->getQuery()->fetchByProductIdentifier(['productA']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnASingleProduct()
    {
        $expected = ['productC' => $this->getAssociationsFormattedAfterFetch(['groupA', 'groupB'], ['groupC'])];
        $actual = $this->getQuery()->fetchByProductIdentifier(['productC']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleSimpleProduct()
    {
        $expected = [
            'productA' => $this->getAssociationsFormattedAfterFetch(),
            'productB' => $this->getAssociationsFormattedAfterFetch([], [], [], ['groupA']),
            'productC' => $this->getAssociationsFormattedAfterFetch(['groupA', 'groupB'], ['groupC'])
        ];
        $actual = $this->getQuery()->fetchByProductIdentifier(['productA', 'productB', 'productC']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleWithProductModels()
    {
        $expected = [
            'productA' => $this->getAssociationsFormattedAfterFetch(),
            'productB' => $this->getAssociationsFormattedAfterFetch([], [], [], ['groupA']),
            'productC' => $this->getAssociationsFormattedAfterFetch(['groupA', 'groupB'], ['groupC']),
            'variant_product_1' => $this->getAssociationsFormattedAfterFetch(['groupF', 'groupD'], ['groupA', 'groupC', 'groupG'], ['groupB'], ['groupE']),
        ];

        $actual = $this->getQuery()->fetchByProductIdentifier(['productA', 'productB', 'productC', 'variant_product_1']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getAssociationsFormattedAfterFetch(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [], array $aNewType = []): array
    {
        return [
            'X_SELL' => ['groups' => $crossSell],
            'PACK' => ['groups' => $pack],
            'SUBSTITUTION' => ['groups' => $substitutions],
            'UPSELL' => ['groups' => $upsell],
            'A_NEW_TYPE' => ['groups' => $aNewType]
        ];
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

    private function givenGroup(array $codes): void
    {
        $groups = array_map(function (string $code) {
            $group = $this->get('pim_catalog.factory.group')->createGroup('RELATED');
            $this->get('pim_catalog.updater.group')->update($group, [
                'code' => $code
            ]);

            $errors = $this->get('validator')->validate($group);

            Assert::count($errors, 0);

            return $group;
        }, $codes);

        $this->get('pim_catalog.saver.group')->saveAll($groups);
    }

    private function getAssociationsFormatted(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [])
    {
        return ['associations' => [
            'X_SELL' => ['groups' => $crossSell],
            'PACK' => ['groups' => $pack],
            'SUBSTITUTION' => ['groups' => $substitutions],
            'UPSELL' => ['groups' => $upsell]
        ]];
    }

    private function getQuery(): GetGroupAssociationsByProductIdentifiers
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_group_associations_by_product_identifiers');
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
                'attributes' => array_merge(['sku'], $data['attribute_codes']),
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
